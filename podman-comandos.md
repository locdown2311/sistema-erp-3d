# Guia de Containerização com Podman Pods (MariaDB + Redis)

Este documento contém os comandos necessários para rodar a aplicação Laravel (ERP 3D) em um ambiente isolado usando **Podman Pods**.
Nessa abordagem **não usamos** o `compose`. Criamos os containers conectando-os ao mesmo Pod, para que consigam conversar internamente pela rede como se estivessem na mesma máquina (via `localhost` ou `127.0.0.1`).

## Pré-requisitos
- Ter o [Podman Desktop](https://podman-desktop.io/) ou `podman` instalado.
- Certificar-se que a porta `8123` está livre.

---

## 🚀 1. Subindo o Sistema do Zero (Primeira Vez)

### Passo A: Criar o Pod Central
Primeiro, criamos a "caixa principal" que vai abrigar as peças e expor as portas para a sua máquina.

```bash
podman pod create --name erp-pod -p 8123:80
```

### Passo B: Subir o Redis (Cache/Sessão/Filas)
Vamos subir um container Redis em background atrelado ao nosso Pod.

```bash
podman run -d --pod erp-pod --name erp-redis docker.io/redis:alpine
```

### Passo C: Subir o MariaDB (Banco de Dados)
Subiremos o MariaDB configurando automaticamente as chaves e usuários propostos no nosso sistema (senhas compatíveis com o seu `.env`). O banco se chamará `erp_3d`, o usuário `erp_user` e a senha `secret`. Criaremos também um volume chamado `erp-db` para persistir os dados.

```bash
podman run -d --pod erp-pod --name erp-db \
  -v erp-db:/var/lib/mysql \
  -e MYSQL_DATABASE=erp_3d \
  -e MYSQL_USER=erp_user \
  -e MYSQL_PASSWORD=secret \
  -e MYSQL_ROOT_PASSWORD=Ic2396g. \
  docker.io/mariadb:10.11
```
*(Aguarde cerca de 20-30 segundos para o MariaDB terminar de iniciar antes de ir para o passo D)*

### Passo D: Fazer o Build e Subir a App Laravel
Agora damos o "build" da imagem local baseada no nosso `Dockerfile` (que tem as dependências de PHP e Node) e então executamos o servidor. Também manteremos os arquivos de uploads persistidos no volume `erp-storage`.

```bash
# Faz o build da imagem customizada (só precisa rodar uma vez ou quando mudar o Dockerfile)
podman build -t localhost/erp-3d-app .

# Sobe o container da App
podman run -d --pod erp-pod --name erp-app -v erp-storage:/var/www/html/storage/app/public localhost/erp-3d-app
podman exec -it erp-app php artisan storage:link  
```


---

## ⚙️ 2. Executando Tarefas Iniciais (Criando as Tabelas)
Como o nosso banco MariaDB subiu vazio e é nosso primeiro acesso, precisamos injetar as tabelas e dados através do artisan do Laravel.

```bash
# Rodar as migrations para criar as tabelas
podman exec -it erp-app php artisan migrate --force

# Recriar os links simbólicos de storage, caso não existam
podman exec -it erp-app php artisan storage:link
```

Pronto! Acesse: **[http://localhost:8123](http://localhost:8123)**

---

## 🔧 3. Comandos Úteis e Operações Comuns

**Derrubar Tudo (Parar e remover todos os containers do Pod):**
```bash
podman pod rm -f erp-pod
```
*(Seus relatórios e sistema sumirão, mas as VENDAS e BANCO ficam salvos nos volumes `erp-db`)*

**Ver Todos os containers rodando do nosso sistema:**
```bash
podman ps -a --pod
```

**Checar os Logs de Erro da App:**
```bash
podman logs erp-app
```

**Acessar o Terminal de um Container:**
```bash
# Entrar no bash do app (PHP)
podman exec -it erp-app bash

# Entrar no terminal do banco de dados (MySQL)
podman exec -it erp-db /bin/bash
```

**Se eu precisar reiniciar apenas o App?**
```bash
podman restart erp-app
```
