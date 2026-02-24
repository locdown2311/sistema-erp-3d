# Changelog — ERP Impressão 3D

## [2026-02-23]

### Corrigido
- **Erro NOT NULL ao criar produto**: Campos `print_time_hours` e `weight_grams` agora recebem valor `0` quando deixados em branco no formulário (`ProductController@store` e `@update`).
- **Erro "active must be true or false" ao editar produto**: Removida validação `'active' => 'boolean'` do `ProductController@update`, pois checkboxes desmarcados não enviam valor. O campo já é tratado manualmente com `$request->has('active')`.
- **Imagem do produto não aparece**: Executado `php artisan storage:link` para criar o symlink `public/storage → storage/app/public`, necessário para servir arquivos uploadados.
- **Timezone alterada para GMT-3**: Adicionado `APP_TIMEZONE=America/Sao_Paulo` no `.env` e locale alterado para `pt_BR`.
- **Calendário não exibia tarefas**: A API retornava `due_date` como datetime ISO (`2026-02-24T00:00:00.000000Z`) mas o JS comparava com `YYYY-MM-DD`. Corrigido para extrair apenas os 10 primeiros caracteres da data antes de comparar.
- **Dashboard não exibia próximas tarefas**: Query usava `now()` (com hora) para comparar com coluna `date`, excluindo tarefas de hoje. Corrigido para `today()` (meia-noite).

### Adicionado
- **Módulo Filamentos**: Nova seção para gerenciar filamentos (PLA, ABS, PETG, TPU, etc.) com nome, tipo, cor, marca, preço/kg, peso total, peso restante (barra visual), diâmetro, temperaturas de impressão/mesa e observações. Acessível na sidebar entre Produtos e Estoque.
- **Filamentos — marca obrigatória e nome automático**: Campo `brand` agora é obrigatório. O `name` é gerado automaticamente como `marca + tipo + cor` (ex: "eSUN PLA Branco"). Campo nome removido do formulário.
- **Filamentos — quantidade de rolos**: Novo campo "Quantidade de Rolos" ao criar filamento. Permite registrar N rolos idênticos de uma vez (ex: 5 rolos de PLA Branco). Campo oculto ao editar.
- **Filamentos — layout temperatura corrigido e sugestão automática**: Tabela agora mostra colunas separadas para Temp. Impressão e Temp. Mesa. Formulário com botão "Sugerir" que preenche temperaturas padrão por material (PLA, ABS, PETG, TPU, Nylon, ASA). Temperaturas auto-preenchidas ao selecionar tipo no cadastro.
- **Filamentos — consumo de filamento**: Botão 🔥 com campo de gramas na tabela para consumir filamento. Deduz o peso restante e atualiza a barra de progresso. Validação impede consumir mais do que o disponível.
- **Produtos — Ctrl+V para colar imagem + recorte**: Upload de imagem agora aceita Ctrl+V (colar da área de transferência), drag-and-drop e seleção de arquivo. Após selecionar/colar, abre modal de recorte com Cropper.js (girar, espelhar, cortar livre). Funciona em Novo Produto e Editar Produto.
- **Sistema de Usuários e Lojas Multi-Tenant**: Cada vendedor cria sua loja (nome, logo, slug, WhatsApp). Login/registro com autenticação. Todos os dados (produtos, filamentos, vendas, estoque, tarefas, custos, configurações) ficam isolados por usuário. Sidebar mostra nome da loja, link "Minha Loja", e botão de logout.
- **Vitrine Pública**: Cada loja tem URL pública `/loja/{slug}` com grid de produtos, imagens e preços. Visitantes veem botão "Comprar via WhatsApp" que abre conversa com mensagem pre-preenchida.
