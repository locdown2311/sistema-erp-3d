# Guia de Containerização com Podman

Este documento contém os comandos necessários para rodar a aplicação Laravel (ERP 3D) utilizando **Podman** e **podman-compose**.

O ambiente foi configurado para usar o Apache com o PHP 8.2 embutido, compilando os assets locais via npm, e utilizando o SQLite como banco de dados persistido via volumes.

## Pré-requisitos
- Ter o [Podman Desktop](https://podman-desktop.io/) ou `podman` / `podman-compose` instalado na sua máquina host (Windows/Linux/Mac).
- O arquivo `.env` deve existir na raiz do projeto contendo a sua `APP_KEY` válida. (Se não tiver, copie o `.env.example` para `.env` e gere a key depois).

---

## 🚀 1. Subindo a Aplicação pela Primeira Vez

### Passo A: Build e Iniciar
Abra o terminal na pasta raiz do projeto (`c:\Users\igor2\OneDrive\Documentos\3D\ideias\sistema-erp-vendas`) e rode:

```bash
podman-compose up -d --build
```
*Isso vai baixar a imagem do PHP, instalar pacotes (.deb e composer), as dependências do Node (Vite), copiar o código e iniciar o container em background (`-d`). Pode demorar alguns minutos na primeira vez.*

### Passo B: Banco de Dados e Permissões Iniciais
Como o sistema usa SQLite via volume persistente, precisamos criar/migrar as tabelas pela primeira vez dentro do container:

```bash
# Rodar as migrations (cria as tabelas no banco de dados vazio)
podman exec -it erp-3d-app php artisan migrate --force

# Criar o link simbólico para as imagens upadas (storage:link)
podman exec -it erp-3d-app php artisan storage:link
```

### Passo C: Acessar no Navegador
O sistema estará rodando em:
👉 **[http://localhost:8000](http://localhost:8000)**

---

## 🔧 2. Comandos Úteis do Dia a Dia

**Derrubar a aplicação (mantendo os dados):**
```bash
podman-compose down
```

**Ver os logs em tempo real:**
```bash
podman-compose logs -f
```

**Acessar o shell do container (Bash) como root:**
```bash
podman exec -it erp-3d-app bash
```

**Limpar os caches do Laravel:**
```bash
podman exec -it erp-3d-app php artisan optimize:clear
```

**Se você modificou arquivos CSS/JS locais:**
Você precisará dar um "rebuild" na imagem para compilar o Vite novamente:
```bash
podman-compose up -d --build
```

---

## 💾 Sobre os Dados (Volumes)
Nós isolamos a pasta `database/` e `storage/app/public/` em volumes do Podman (definidos no `podman-compose.yml`).
Isso significa que, mesmo que você apague o container (`podman-compose down`), **suas vendas, produtos e arquivos upados não serão perdidos.**
Para apagar COMPLETAMENTE os dados salvos e resetar o sistema, você precisaria deletar os volumes rodando: `podman-compose down -v`.
