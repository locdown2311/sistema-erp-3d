<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    /**
     * Busca o rastreamento via SeuRastreio API public
     */
    public function track($code)
    {
        // Validação básica do código de rastreio (padrão Correios Brasil: 2 letras, 9 números, 2 letras)
        if (!preg_match('/^[A-Z]{2}[0-9]{9}[A-Z]{2}$/i', $code)) {
            return response()->json([
                'success' => false,
                'message' => 'Código de rastreio em formato inválido.'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer sr_live_-gzZOgaTLywX_Yu1RcbYaAWZuUR4ihpEfjzdvRrRiwk'
            ])->timeout(10)->get('https://seurastreio.com.br/api/public/rastreio/' . strtoupper($code));

            if ($response->successful()) {
                $data = $response->json();
                
                // Se o status retornado pela API not_found
                if (isset($data['status']) && $data['status'] === 'not_found') {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Código inválido ou recém postado.',
                        'raw' => $data
                    ]);
                }

                // Procurar eventos na resposta
                $events = [];
                if (isset($data['eventos']) && is_array($data['eventos'])) {
                    $events = $data['eventos'];
                } elseif (isset($data['eventoMaisRecente'])) {
                    // API do SeuRastreio retorna apenas o evento mais recente neste endpoint
                    $events = [$data['eventoMaisRecente']];
                }

                // Adapter para o nosso frontend consumir a timeline
                $adaptedEvents = [];
                if (!empty($events) && is_array($events)) {
                    foreach($events as $ev) {
                        // Tratar Data e Hora (Padrão ISO8601 ex: 2024-01-15T14:30:00.000Z)
                        $dateStr = '--/--/----';
                        $timeStr = '--:--';
                        if (!empty($ev['data'])) {
                            try {
                                $dt = new \DateTime($ev['data']);
                                $dt->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
                                $dateStr = $dt->format('d/m/Y');
                                $timeStr = $dt->format('H:i');
                            } catch (\Throwable $th) {}
                        }

                        // Status com detalhe opcional
                        $status = $ev['descricao'] ?? $ev['status'] ?? 'Evento Registrado';
                        if (!empty($ev['detalhe'])) {
                            $status .= ' (' . $ev['detalhe'] . ')';
                        }

                        $adaptedEvents[] = [
                            'data' => $dateStr,
                            'hora' => $timeStr,
                            'local' => $ev['local'] ?? 'Local não informado',
                            'status' => $status,
                            'link' => $data['linkDetalhesCompletos'] ?? null
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'events' => $adaptedEvents,
                    'link' => $data['linkDetalhesCompletos'] ?? null,
                    'raw' => $data // Mantemos o raw para debug no front
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Serviço SeuRastreio fora do ar.',
                'status' => $response->status()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao consultar o serviço de frete.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
