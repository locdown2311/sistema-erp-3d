<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use stdClass;

class NFeController extends Controller
{
    public function index()
    {
        $products = auth()->user()->products()->orderBy('name')->where('active', true)->get();
        $emit_nome = Setting::get('nfe_emit_nome', 'EMPRESA DE IMPRESSÃO 3D LTDA');
        $emit_cnpj = Setting::get('nfe_emit_cnpj', '01234567890123');
        $emit_ie = Setting::get('nfe_emit_ie', '111111111111');
        
        return view('nfe.index', compact('products', 'emit_nome', 'emit_cnpj', 'emit_ie'));
    }

    private function getToolsConfig()
    {
        $ambiente = Setting::get('nfe_ambiente', 2);
        $uf = Setting::get('nfe_uf', 'SP');
        $cnpj = Setting::where('key', 'emit_cnpj')->first() ? preg_replace('/[^0-9]/', '', Setting::get('emit_cnpj')) : '01234567890123';
        
        $config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int)$ambiente,
            "razaosocial" => Setting::get('company_name', 'Minha Empresa 3D'),
            "siglaUF" => strtoupper($uf),
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ];

        return json_encode($config);
    }

    public function emit(Request $request)
    {
        $validated = $request->validate([
            'emit_nome' => 'required|string',
            'emit_cnpj' => 'required|string',
            'emit_ie' => 'required|string',
            'dest_nome' => 'required|string',
            'dest_cpf' => 'required|string',
            'dest_cep' => 'required|string|size:9',
            'dest_logradouro' => 'required|string',
            'dest_numero' => 'required|string',
            'dest_bairro' => 'required|string',
            'dest_municipio' => 'required|string',
            'dest_uf' => 'required|string|size:2',
            'prod_descricao' => 'required|array|min:1',
            'prod_ncm' => 'required|array',
            'prod_cfop' => 'required|array',
            'prod_qtd' => 'required|array',
            'prod_vlr_unit' => 'required|array',
            'prod_vlr_total' => 'required|array',
            'prod_descricao.*' => 'required|string',
            'prod_qtd.*' => 'required|numeric',
            'prod_vlr_unit.*' => 'required|numeric',
            'prod_vlr_total.*' => 'required|numeric',
        ]);

        $nfe = new Make();
        
        // infNFe
        $std = new stdClass();
        $std->versao = '4.00';
        $std->Id = '';
        $std->pk_nItem = null;
        $nfe->taginfNFe($std);
        
        // ide
        $std = new stdClass();
        $std->cUF = 35; // SP
        $std->cNF = '80080004';
        $std->natOp = 'VENDA DE PRODUTO';
        $std->mod = 55;
        $std->serie = 1;
        $std->nNF = 2;
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->dhSaiEnt = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = 1;
        $std->cMunFG = 3550308; // SP
        $std->tpImp = 1;
        $std->tpEmis = 1;
        $std->cDV = 2;
        $std->tpAmb = 2; // Homologação
        $std->finNFe = 1;
        $std->indFinal = 1;
        $std->indPres = 1;
        $std->indIntermed = 0;
        $std->procEmi = 0;
        $std->verProc = '3.10.31';
        $nfe->tagide($std);
        
        // emitente
        $std = new stdClass();
        $std->xNome = $validated['emit_nome'];
        $std->xFant = $validated['emit_nome'];
        $std->IE = $validated['emit_ie'];
        $std->CRT = 3;
        $std->CNPJ = preg_replace('/[^0-9]/', '', $validated['emit_cnpj']);
        $nfe->tagemit($std);
        
        $std = new stdClass();
        $std->xLgr = Setting::get('nfe_emit_logradouro', 'Rua Padrão');
        $std->nro = Setting::get('nfe_emit_numero', '123');
        $std->xBairro = Setting::get('nfe_emit_bairro', 'Bairro Padrão');
        $std->cMun = 3550308; // SP default IBGE
        $std->xMun = Setting::get('nfe_emit_municipio', 'São Paulo');
        $std->UF = Setting::get('nfe_emit_uf', 'SP');
        $std->CEP = preg_replace('/[^0-9]/', '', Setting::get('nfe_emit_cep', '01000000'));
        $std->cPais = 1058;
        $std->xPais = 'Brasil';
        $nfe->tagenderEmit($std);
        
        // destinatario
        $std = new stdClass();
        $std->xNome = $validated['dest_nome'];
        $std->indIEDest = 9;
        $std->IE = '';
        
        $doc = preg_replace('/[^0-9]/', '', $validated['dest_cpf']);
        if (strlen($doc) === 14) {
            $std->CNPJ = $doc;
        } else {
            $std->CPF = $doc;
        }
        $nfe->tagdest($std);
        
        $std = new stdClass();
        $std->xLgr = $validated['dest_logradouro'];
        $std->nro = $validated['dest_numero'];
        $std->xBairro = $validated['dest_bairro'];
        $std->cMun = 3550308; // IBGE code
        $std->xMun = $validated['dest_municipio'];
        $std->UF = $validated['dest_uf'];
        $std->CEP = preg_replace('/[^0-9]/', '', $validated['dest_cep']);
        $std->cPais = 1058;
        $std->xPais = 'Brasil';
        $nfe->tagenderDest($std);

        // Variáveis para somatórios de TOTAIS
        $vbcGlobal = 0.00;
        $vicmsGlobal = 0.00;
        $vpisGlobal = 0.00;
        $vcofinsGlobal = 0.00;
        $vprodGlobal = 0.00;

        foreach ($validated['prod_descricao'] as $index => $descricao) {
            $item_number = $index + 1;
            $qtd = (float) $validated['prod_qtd'][$index];
            $vUnit = (float) $validated['prod_vlr_unit'][$index];
            $vTot = (float) $validated['prod_vlr_total'][$index];

            // produto
            $std = new stdClass();
            $std->item = $item_number;
            $std->cProd = str_pad($item_number, 4, '0', STR_PAD_LEFT);
            $std->cEAN = 'SEM GTIN';
            $std->xProd = $descricao;
            $std->NCM = preg_replace('/[^0-9]/', '', $validated['prod_ncm'][$index]);
            $std->CFOP = preg_replace('/[^0-9]/', '', $validated['prod_cfop'][$index]);
            $std->uCom = 'UN';
            $std->qCom = number_format($qtd, 4, '.', '');
            $std->vUnCom = number_format($vUnit, 4, '.', '');
            $std->vProd = number_format($vTot, 2, '.', '');
            $std->cEANTrib = 'SEM GTIN';
            $std->uTrib = 'UN';
            $std->qTrib = number_format($qtd, 4, '.', '');
            $std->vUnTrib = number_format($vUnit, 4, '.', '');
            $std->indTot = 1;
            $nfe->tagprod($std);

            // imposto tag base
            $std = new stdClass();
            $std->item = $item_number;
            $nfe->tagimposto($std);
            
            // Simplificado, ICMS base
            $std = new stdClass();
            $std->item = $item_number;
            $std->orig = 0;
            $std->CST = '00';
            $std->modBC = 0;
            $std->vBC = number_format($vTot, 2, '.', '');
            $std->pICMS = '18.0000';
            $vIcms = ($vTot * 18) / 100;
            $std->vICMS = number_format($vIcms, 2, '.', '');
            $nfe->tagICMS($std);

            // PIS/COFINS mockados para evitar erros de validação
            $std = new stdClass();
            $std->item = $item_number;
            $std->CST = '01';
            $std->vBC = number_format($vTot, 2, '.', '');
            $std->pPIS = '1.6500';
            $vPis = ($vTot * 1.65) / 100;
            $std->vPIS = number_format($vPis, 2, '.', '');
            $nfe->tagPIS($std);

            $std = new stdClass();
            $std->item = $item_number;
            $std->CST = '01';
            $std->vBC = number_format($vTot, 2, '.', '');
            $std->pCOFINS = '7.6000';
            $vCofins = ($vTot * 7.6) / 100;
            $std->vCOFINS = number_format($vCofins, 2, '.', '');
            $nfe->tagCOFINS($std);

            // Somar acumulados
            $vbcGlobal += $vTot;
            $vicmsGlobal += $vIcms;
            $vpisGlobal += $vPis;
            $vcofinsGlobal += $vCofins;
            $vprodGlobal += $vTot;
        }

        // Totais Globais da NF-e
        $std = new stdClass();
        $std->vBC = number_format($vbcGlobal, 2, '.', '');
        $std->vICMS = number_format($vicmsGlobal, 2, '.', '');
        $std->vICMSDeson = '0.00';
        $std->vFCP = '0.00';
        $std->vBCST = '0.00';
        $std->vST = '0.00';
        $std->vFCPST = '0.00';
        $std->vFCPSTRet = '0.00';
        $std->vProd = number_format($vprodGlobal, 2, '.', '');
        $std->vFrete = '0.00';
        $std->vSeg = '0.00';
        $std->vDesc = '0.00';
        $std->vII = '0.00';
        $std->vIPI = '0.00';
        $std->vIPIDevol = '0.00';
        $std->vPIS = number_format($vpisGlobal, 2, '.', '');
        $std->vCOFINS = number_format($vcofinsGlobal, 2, '.', '');
        $std->vOutro = '0.00';
        $std->vNF = number_format($vprodGlobal, 2, '.', ''); // Total da nota é o total dos produtos (sem frete/descontos nessta versão)
        $nfe->tagICMSTot($std);
        
        // frete
        $std = new stdClass();
        $std->modFrete = 9; // Sem frete
        $nfe->tagtransp($std);

        // pagamentos globais (A vista, valor total da nota)
        $std = new stdClass();
        $std->vTroco = '0.00';
        $nfe->tagpag($std);
        
        $std = new stdClass();
        $std->indPag = 0; // A vista
        $std->tPag = '01'; // Dinheiro
        $std->vPag = number_format($vprodGlobal, 2, '.', '');
        $nfe->tagdetPag($std);
        
        try {
            // Executa a montagem verificando erros na validação lógica dos nós
            $nfe->montaNFe();
            $xml = $nfe->getXML();
            $errors = $nfe->getErrors();
            
            if (!empty($errors)) {
                return response()->json([
                    'error' => 'Erro na validação do XML da NF-e',
                    'validation_errors' => $errors
                ], 422);
            }
            
            // Tentar assinar o XML caso o certificado esteja configurado
            $certificadoPath = Setting::get('nfe_certificado_path');
            $certificadoSenha = Setting::get('nfe_certificado_senha') ? decrypt(Setting::get('nfe_certificado_senha')) : '';

            if ($certificadoPath && Storage::disk('local')->exists($certificadoPath)) {
                $pfxContent = Storage::disk('local')->get($certificadoPath);
                
                try {
                    $certificate = Certificate::readPfx($pfxContent, $certificadoSenha);
                    $tools = new Tools($this->getToolsConfig(), $certificate);
                    
                    // Assina a NFe
                    $xmlAssinado = $tools->signNFe($xml);
                    
                    // TODO: Aqui entraria o envio ($tools->sefazEnviaLote([$xmlAssinado], 1))
                    
                    return response($xmlAssinado, 200)
                        ->header('Content-Type', 'text/xml')
                        ->header('Content-Disposition', 'attachment; filename="nfe_assinado_' . time() . '.xml"');
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Erro ao assinar o XML com o Certificado Digital',
                        'exception' => $e->getMessage(),
                    ], 500);
                }
            }

            // Fallback se não tiver certificado, retorna o XML sem assinar (Apenas o Protótipo de Construção)
            return response($xml, 200)
                ->header('Content-Type', 'text/xml')
                ->header('Content-Disposition', 'attachment; filename="nfe_prototipo_' . time() . '.xml"');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro inesperado ao gerar XML',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }
}
