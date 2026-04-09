<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use stdClass;

class NFeController extends Controller
{
    public function index()
    {
        if (!(auth()->user()->currentPlan()?->can_use_nfe ?? false)) {
            abort(403, 'A emissão de Notas Fiscais (NF-e) requer um plano com suporte a este recurso.');
        }

        $products = auth()->user()->products()->orderBy('name')->where('active', true)->get();
        
        // Fetch last 50 completed sales for the auto-fill dropdown
        $sales = auth()->user()->sales()
            ->with(['items.product', 'customer'])
            ->where('status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'reference' => 'Venda #' . $sale->id,
                    'date' => $sale->sale_date,
                    'total' => $sale->total,
                    'customer' => $sale->customer ? [
                        'name' => $sale->customer->name,
                        'document' => $sale->customer->document,
                        'ie' => $sale->customer->ie,
                        'cep' => $sale->customer->cep,
                        'address' => $sale->customer->address,
                        'number' => $sale->customer->number,
                        'neighborhood' => $sale->customer->neighborhood,
                        'city' => $sale->customer->city,
                        'state' => $sale->customer->state,
                    ] : null,
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product ? $item->product->name : 'Produto Removido',
                            'ncm' => $item->product ? $item->product->ncm : '',
                            'quantity' => $item->quantity,
                            'price' => $item->unit_price,
                            'total' => $item->total_price,
                        ];
                    }),
                ];
            });

        $emit_nome = Setting::get('nfe_emit_nome');
        $emit_cnpj = Setting::get('nfe_emit_cnpj');
        $emit_ie   = Setting::get('nfe_emit_ie');

        return \Inertia\Inertia::render('NFe/Index', [
            'products' => $products,
            'sales' => $sales,
            'settings' => [
                'emit_nome' => $emit_nome,
                'emit_cnpj' => $emit_cnpj,
                'emit_ie'   => $emit_ie,
                'default_cfop' => Setting::get('nfe_default_cfop', '5102'),
            ]
        ]);
    }

    /**
     * Monta a configuração JSON exigida pelo NFePHP Tools.
     * Todos os valores vêm das Settings do usuário.
     */
    private function getToolsConfig(): string
    {
        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) Setting::get('nfe_ambiente', 2),
            'razaosocial'  => Setting::get('nfe_emit_nome', ''),
            'siglaUF'      => strtoupper(Setting::get('nfe_uf', 'SP')),
            'cnpj'         => preg_replace('/[^0-9]/', '', Setting::get('nfe_emit_cnpj', '')),
            'schemes'      => 'PL_009_V4',
            'versao'       => '4.00',
        ];

        return json_encode($config);
    }

    /**
     * Retorna o código numérico da UF (IBGE) a partir da sigla.
     */
    private function ufCode(string $uf): int
    {
        $map = [
            'AC' => 12, 'AL' => 27, 'AM' => 13, 'AP' => 16, 'BA' => 29,
            'CE' => 23, 'DF' => 53, 'ES' => 32, 'GO' => 52, 'MA' => 21,
            'MG' => 31, 'MS' => 50, 'MT' => 51, 'PA' => 15, 'PB' => 25,
            'PE' => 26, 'PI' => 22, 'PR' => 41, 'RJ' => 33, 'RN' => 24,
            'RO' => 11, 'RR' => 14, 'RS' => 43, 'SC' => 42, 'SE' => 28,
            'SP' => 35, 'TO' => 17,
        ];

        return $map[strtoupper($uf)] ?? 35;
    }

    /**
     * Emissão da NF-e: monta XML, assina, envia via sefazEnviaLote e
     * consulta o recibo para retornar o protocolo de autorização.
     */
    public function emit(Request $request)
    {
        if (!(auth()->user()->currentPlan()?->can_use_nfe ?? false)) {
            return response()->json(['error' => 'A emissão de Notas Fiscais (NF-e) requer um plano com suporte a este recurso.'], 403);
        }

        // ── Validação dos dados do formulário ──────────────────────
        $validated = $request->validate([
            'emit_nome'          => 'required|string',
            'emit_cnpj'          => 'required|string',
            'emit_ie'            => 'required|string',
            'dest_nome'          => 'required|string',
            'dest_cpf'           => 'required|string',
            'dest_cep'           => 'required|string|size:9',
            'dest_logradouro'    => 'required|string',
            'dest_numero'        => 'required|string',
            'dest_bairro'        => 'required|string',
            'dest_municipio'     => 'required|string',
            'dest_uf'            => 'required|string|size:2',
            'dest_ie'            => 'nullable|string|max:20',
            'dest_cmun'          => 'nullable|string',
            'regime_tributario'  => 'required|string',
            'is_draft'           => 'boolean',
            'prod_descricao'     => 'required|array|min:1',
            'prod_ncm'           => 'required|array',
            'prod_cfop'          => 'required|array',
            'prod_qtd'           => 'required|array',
            'prod_vlr_unit'      => 'required|array',
            'prod_vlr_total'     => 'required|array',
            'prod_descricao.*'   => 'required|string',
            'prod_qtd.*'         => 'required|numeric',
            'prod_vlr_unit.*'    => 'required|numeric',
            'prod_vlr_total.*'   => 'required|numeric',
        ]);

        // ── Carregar configurações do emitente via Settings ────────
        $emitUf      = strtoupper(Setting::get('nfe_uf', 'SP'));
        $emitCuf     = $this->ufCode($emitUf);
        $emitCMun    = (int) Setting::get('nfe_emit_cmun', 3550308);
        $tpAmb       = (int) Setting::get('nfe_ambiente', 2);
        $serie       = (int) Setting::get('nfe_serie', 1);
        $proximaNNF  = (int) Setting::get('nfe_proxima_nnf', 1);

        // ── Certificado digital ───────────────────────────────────
        $certificadoPath  = Setting::get('nfe_certificado_path');
        $certificadoSenha = Setting::get('nfe_certificado_senha')
            ? decrypt(Setting::get('nfe_certificado_senha'))
            : '';

        $temCertificado = $certificadoPath && Storage::disk('local')->exists($certificadoPath);
        $tools          = null;

        if ($temCertificado) {
            $pfxContent  = Storage::disk('local')->get($certificadoPath);
            $certificate = Certificate::readPfx($pfxContent, $certificadoSenha);
            $tools       = new Tools($this->getToolsConfig(), $certificate);
        } elseif ($tpAmb === 1) {
            // Em produção o certificado é obrigatório
            return redirect()->back()->with('error', 'Certificado digital A1 não configurado. Vá em Configurações e faça o upload do seu certificado .pfx.');
        }

        // ── Montagem do XML ───────────────────────────────────────
        $nfe = new Make();

        // infNFe
        $std = new stdClass();
        $std->versao   = '4.00';
        $std->Id       = '';
        $std->pk_nItem = null;
        $nfe->taginfNFe($std);

        // ide (dados da nota)
        $cNF = str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);

        $std = new stdClass();
        $std->cUF        = $emitCuf;
        $std->cNF        = $cNF;
        $std->natOp      = Setting::get('nfe_nat_op', 'VENDA DE PRODUTO');
        $std->mod        = 55;
        $std->serie      = $serie;
        $std->nNF        = $proximaNNF;
        $std->dhEmi      = date("Y-m-d\TH:i:sP");
        $std->dhSaiEnt   = date("Y-m-d\TH:i:sP");
        $std->tpNF       = 1;
        $std->idDest     = 1;
        $std->cMunFG     = $emitCMun;
        $std->tpImp      = 1;
        $std->tpEmis     = 1;
        $std->cDV        = 0;
        $std->tpAmb      = $tpAmb;
        $std->finNFe     = 1;
        $std->indFinal   = 1;
        $std->indPres    = 1;
        $std->indIntermed = 0;
        $std->procEmi    = 0;
        $std->verProc    = 'Sys3D 1.0.0';
        $nfe->tagide($std);

        $regime = $validated['regime_tributario'] ?? '1'; // 1=Simples, 2=MEI, 3=Normal

        // emitente
        $std = new stdClass();
        $std->xNome = $validated['emit_nome'];
        $std->xFant = $validated['emit_nome'];
        $std->IE    = preg_replace('/[^0-9]/', '', $validated['emit_ie']);
        // CRT 1 = Simples Nacional, 2 = Simples Nacional (excesso de sublimite), 3 = Regime Normal
        // We map MEI(2) frontend to CRT 1 as NFePHP expects CRT 1 for MEI.
        $std->CRT   = ($regime === '3') ? 3 : 1; 
        $std->CNPJ  = preg_replace('/[^0-9]/', '', $validated['emit_cnpj']);
        $nfe->tagemit($std);

        $std = new stdClass();
        $std->xLgr    = Setting::get('nfe_emit_logradouro', '');
        $std->nro      = Setting::get('nfe_emit_numero', 'S/N');
        $std->xBairro  = Setting::get('nfe_emit_bairro', '');
        $std->cMun     = $emitCMun;
        $std->xMun     = Setting::get('nfe_emit_municipio', '');
        $std->UF       = $emitUf;
        $std->CEP      = preg_replace('/[^0-9]/', '', Setting::get('nfe_emit_cep', ''));
        $std->cPais    = 1058;
        $std->xPais    = 'Brasil';
        $nfe->tagenderEmit($std);

        // destinatário
        $destIE = $request->input('dest_ie') ? preg_replace('/[^0-9]/', '', $request->input('dest_ie')) : '';

        $std = new stdClass();
        $std->xNome     = $validated['dest_nome'];
        $std->indIEDest = $destIE ? 1 : 9; // 1 = Contribuinte ICMS, 9 = Não contribuinte
        $std->IE        = $destIE;

        $doc = preg_replace('/[^0-9]/', '', $validated['dest_cpf']);
        if (strlen($doc) === 14) {
            $std->CNPJ = $doc;
        } else {
            $std->CPF = $doc;
        }
        $nfe->tagdest($std);

        $destCMun = $request->input('dest_cmun')
            ? (int) preg_replace('/[^0-9]/', '', $request->input('dest_cmun'))
            : $emitCMun;

        $std = new stdClass();
        $std->xLgr    = $validated['dest_logradouro'];
        $std->nro      = $validated['dest_numero'];
        $std->xBairro  = $validated['dest_bairro'];
        $std->cMun     = $destCMun;
        $std->xMun     = $validated['dest_municipio'];
        $std->UF       = strtoupper($validated['dest_uf']);
        $std->CEP      = preg_replace('/[^0-9]/', '', $validated['dest_cep']);
        $std->cPais    = 1058;
        $std->xPais    = 'Brasil';
        $nfe->tagenderDest($std);

        // ── Produtos e impostos ───────────────────────────────────
        $vbcGlobal     = 0.00;
        $vicmsGlobal   = 0.00;
        $vipiGlobal    = 0.00;
        $vpisGlobal    = 0.00;
        $vcofinsGlobal = 0.00;
        $vprodGlobal   = 0.00;

        $pICMS   = (float) Setting::get('nfe_picms', 18);
        $pIPI    = (float) Setting::get('nfe_pipi', 5);
        $pPIS    = (float) Setting::get('nfe_ppis', 1.65);
        $pCOFINS = (float) Setting::get('nfe_pcofins', 7.6);

        $regime = $validated['regime_tributario'] ?? '1'; // 1=Simples, 2=MEI, 3=Normal

        foreach ($validated['prod_descricao'] as $index => $descricao) {
            $item  = $index + 1;
            $qtd   = (float) $validated['prod_qtd'][$index];
            $vUnit = (float) $validated['prod_vlr_unit'][$index];
            $vTot  = (float) $validated['prod_vlr_total'][$index];

            // produto
            $std = new stdClass();
            $std->item     = $item;
            $std->cProd    = str_pad($item, 4, '0', STR_PAD_LEFT);
            $std->cEAN     = 'SEM GTIN';
            $std->xProd    = $descricao;
            $std->NCM      = preg_replace('/[^0-9]/', '', $validated['prod_ncm'][$index]);
            $std->CFOP     = preg_replace('/[^0-9]/', '', $validated['prod_cfop'][$index]);
            $std->uCom     = 'UN';
            $std->qCom     = number_format($qtd, 4, '.', '');
            $std->vUnCom   = number_format($vUnit, 4, '.', '');
            $std->vProd    = number_format($vTot, 2, '.', '');
            $std->cEANTrib = 'SEM GTIN';
            $std->uTrib    = 'UN';
            $std->qTrib    = number_format($qtd, 4, '.', '');
            $std->vUnTrib  = number_format($vUnit, 4, '.', '');
            $std->indTot   = 1;
            $nfe->tagprod($std);

            // imposto (tag base)
            $std = new stdClass();
            $std->item = $item;
            $nfe->tagimposto($std);

            if ($regime === '3') {
                // REGIME NORMAL (Lucro Presumido / Real) - Destaque de impostos
                
                // ICMS (CST 00 - Tributada Integralmente)
                $vIcms = ($vTot * $pICMS) / 100;
                $std = new stdClass();
                $std->item   = $item;
                $std->orig   = 0;
                $std->CST    = '00';
                $std->modBC  = 3; // Valor da operação
                $std->vBC    = number_format($vTot, 2, '.', '');
                $std->pICMS  = number_format($pICMS, 4, '.', '');
                $std->vICMS  = number_format($vIcms, 2, '.', '');
                $nfe->tagICMS($std);

                // IPI (CST 50 - Saída Tributada)
                $vIpi = ($vTot * $pIPI) / 100;
                $std = new stdClass();
                $std->item = $item;
                $std->cEnq = '999'; // Outros (Padrão genérico)
                $std->CST  = '50';
                $std->vBC  = number_format($vTot, 2, '.', '');
                $std->pIPI = number_format($pIPI, 4, '.', '');
                $std->vIPI = number_format($vIpi, 2, '.', '');
                $nfe->tagIPI($std);

                // PIS (CST 01 - Operação Tributável)
                $vPis = ($vTot * $pPIS) / 100;
                $std = new stdClass();
                $std->item = $item;
                $std->CST  = '01';
                $std->vBC  = number_format($vTot, 2, '.', '');
                $std->pPIS = number_format($pPIS, 4, '.', '');
                $std->vPIS = number_format($vPis, 2, '.', '');
                $nfe->tagPIS($std);

                // COFINS (CST 01 - Operação Tributável)
                $vCofins = ($vTot * $pCOFINS) / 100;
                $std = new stdClass();
                $std->item    = $item;
                $std->CST     = '01';
                $std->vBC     = number_format($vTot, 2, '.', '');
                $std->pCOFINS = number_format($pCOFINS, 4, '.', '');
                $std->vCOFINS = number_format($vCofins, 2, '.', '');
                $nfe->tagCOFINS($std);

                $vicmsGlobal   += $vIcms;
                $vipiGlobal    += $vIpi;
                $vpisGlobal    += $vPis;
                $vcofinsGlobal += $vCofins;

            } else {
                // SIMPLES NACIONAL (1) ou MEI (2) - Sem destaque na nota padrão
                
                // ICMS (CSOSN 102 - Tributada pelo Simples Nacional sem permissão de crédito, ou CSOSN 400 - Não tributada/MEI)
                $std = new stdClass();
                $std->item  = $item;
                $std->orig  = 0;
                $std->CSOSN = ($regime === '2') ? '400' : '102';
                $nfe->tagICMSSN($std);

                // PIS (CST 49 - Outras Operações de Saída)
                $std = new stdClass();
                $std->item   = $item;
                $std->CST    = '49';
                $std->vBC    = '0.00';
                $std->pPIS   = '0.0000';
                $std->vPIS   = '0.00';
                $nfe->tagPIS($std);

                // COFINS (CST 49 - Outras Operações de Saída)
                $std = new stdClass();
                $std->item    = $item;
                $std->CST     = '49';
                $std->vBC     = '0.00';
                $std->pCOFINS = '0.0000';
                $std->vCOFINS = '0.00';
                $nfe->tagCOFINS($std);
            }

            // Acumuladores globais
            $vbcGlobal   += ($regime === '3') ? $vTot : 0;
            $vprodGlobal += $vTot;
        }

        // ── Totais ────────────────────────────────────────────────
        $std = new stdClass();
        $std->vBC        = number_format($vbcGlobal, 2, '.', '');
        $std->vICMS      = number_format($vicmsGlobal, 2, '.', '');
        $std->vICMSDeson = '0.00';
        $std->vFCP       = '0.00';
        $std->vBCST      = '0.00';
        $std->vST        = '0.00';
        $std->vFCPST     = '0.00';
        $std->vFCPSTRet  = '0.00';
        $std->vProd      = number_format($vprodGlobal, 2, '.', '');
        $std->vFrete     = '0.00';
        $std->vSeg       = '0.00';
        $std->vDesc      = '0.00';
        $std->vII        = '0.00';
        $std->vIPI       = number_format($vipiGlobal, 2, '.', '');
        $std->vIPIDevol  = '0.00';
        $std->vPIS       = number_format($vpisGlobal, 2, '.', '');
        $std->vCOFINS    = number_format($vcofinsGlobal, 2, '.', '');
        $std->vOutro     = '0.00';
        $std->vNF        = number_format($vprodGlobal + $vipiGlobal, 2, '.', ''); // Total da nota soma o IPI além dos produtos
        $nfe->tagICMSTot($std);

        // ── Transporte ────────────────────────────────────────────
        $std = new stdClass();
        $std->modFrete = 9; // Sem frete
        $nfe->tagtransp($std);

        // ── Pagamento ─────────────────────────────────────────────
        $std = new stdClass();
        $std->vTroco = '0.00';
        $nfe->tagpag($std);

        $std = new stdClass();
        $std->indPag = 0;
        $std->tPag   = '01';
        $std->vPag   = number_format($vprodGlobal, 2, '.', '');
        $nfe->tagdetPag($std);

        // ── Montagem, Assinatura e Envio ──────────────────────────
        try {
            $nfe->montaNFe();
            $xml    = $nfe->getXML();
            $errors = $nfe->getErrors();

            if (!empty($errors)) {
                return redirect()->back()->with('error', 'Erro na validação do XML estrutural da NF-e: ' . implode('<br>', $errors));
            }

            // ── Modo Teste (sem certificado) ──────────────────────
            // Em homologação (tpAmb=2) e sem certificado, retorna o XML
            // sem assinar para que o usuário possa testar a montagem.
            if (!$temCertificado) {
                Setting::set('nfe_proxima_nnf', $proximaNNF + 1);

                $filename = 'nfe_teste_' . $proximaNNF . '.xml';
                Storage::disk('local')->put('nfe_xmls/' . $filename, $xml);

                return redirect()->back()
                    ->with('success', 'NFe de Teste gerada com sucesso!')
                    ->with('download_xml', route('nfe.download', $filename));
            }

            // ── Modo Produção (com certificado): Assinar e Enviar ─
            $xmlAssinado = $tools->signNFe($xml);

            // ── Salvar Rascunho / Apenas Assinar (sem enviar para SEFAZ) ──
            if ($request->boolean('is_draft')) {
                Setting::set('nfe_proxima_nnf', $proximaNNF + 1);

                $filename = 'nfe_' . $proximaNNF . '_rascunho_assinado.xml';
                Storage::disk('local')->put('nfe_xmls/' . $filename, $xmlAssinado);

                return redirect()->back()
                    ->with('success', 'Rascunho assinado com sucesso! O XML foi gerado, mas não foi enviado à SEFAZ.')
                    ->with('download_xml', route('nfe.download', $filename));
            }

            // Enviar lote para a SEFAZ
            $idLote   = str_pad(random_int(1, 999999999999999), 15, '0', STR_PAD_LEFT);
            $resposta = $tools->sefazEnviaLote([$xmlAssinado], $idLote);

            $st       = new Standardize($resposta);
            $stdResp  = $st->toStd();

            // Verificar se o lote foi recebido com sucesso (cStat 103 = Lote recebido)
            if (!isset($stdResp->cStat) || (int) $stdResp->cStat !== 103) {
                return redirect()->back()->with('error', 'SEFAZ rejeitou o lote de Notas Fiscais. <br>Motivo: ' . ($stdResp->xMotivo ?? 'Desconhecido'));
            }

            // Consultar recibo para obter protocolo de autorização
            $nRec = $stdResp->infRec->nRec;

            sleep(3);

            $protocolo = $tools->sefazConsultaRecibo($nRec);
            $stProt    = new Standardize($protocolo);
            $stdProt   = $stProt->toStd();

            // Verificar autorização (cStat 104 = Lote processado)
            if (isset($stdProt->protNFe->infProt->cStat)) {
                $cStatProt = (int) $stdProt->protNFe->infProt->cStat;

                if ($cStatProt === 100) {
                    Setting::set('nfe_proxima_nnf', $proximaNNF + 1);

                    $xmlProtocolado = Complements::toAuthorize($xmlAssinado, $protocolo);
                    
                    $filename = 'nfe_' . $proximaNNF . '_autorizada.xml';
                    Storage::disk('local')->put('nfe_xmls/' . $filename, $xmlProtocolado);

                    return redirect()->back()
                        ->with('success', 'NFe autorizada com sucesso!')
                        ->with('download_xml', route('nfe.download', $filename));
                }

                return redirect()->back()->with('error', 'NF-e rejeitada pela Sefaz. <br>Motivo: ' . ($stdProt->protNFe->infProt->xMotivo ?? 'Desconhecido'));
            }

            return response()->json([
                'warning' => 'O lote foi enviado mas o recibo ainda não foi processado. Tente consultar novamente.',
                'nRec'    => $nRec,
            ], 202);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro inesperado ao gerar/assinar o arquivo XML: ' . $e->getMessage());
        }
    }

    public function download(Request $request, $file)
    {
        if (!(auth()->user()->currentPlan()?->can_use_nfe ?? false)) {
            abort(403);
        }

        $path = 'nfe_xmls/' . $file;
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'XML não encontrado.');
        }

        return Storage::disk('local')->download($path);
    }
}
