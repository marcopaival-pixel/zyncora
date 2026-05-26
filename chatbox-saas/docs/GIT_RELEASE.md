# Git e release v1.0.0

O repositório Git foi inicializado na raiz `ProjetoChatbot` em 2026-05-26 com tag **`v1.0.0`**.

## 1. Estado actual

```powershell
cd c:\Projetos\ProjetoChatbot
& "C:\Program Files\Git\bin\git.exe" log -1 --oneline
& "C:\Program Files\Git\bin\git.exe" tag -l
```

Commit inicial e tag **`v1.0.0`** já criados localmente. Para republicar noutra máquina ou após clone:

## 2. Inicializar repositório (se ainda não existir)

```powershell
& "C:\Program Files\Git\bin\git.exe" commit -m "$( @'
Release Chatbox SaaS 1.0.0 — go-live P0-P12.

Inclui billing Stripe/MP, RBAC, monitorização, grace period, testes e docs operacionais.
'@ )"
```

## 3. Tag anotada

```powershell
& "C:\Program Files\Git\bin\git.exe" tag -a v1.0.0 -m "Chatbox SaaS 1.0.0 — go-live"
& "C:\Program Files\Git\bin\git.exe" tag -l
```

## 4. Remote (quando existir)

```powershell
& "C:\Program Files\Git\bin\git.exe" remote add origin https://github.com/SUA-ORG/ProjetoChatbot.git
& "C:\Program Files\Git\bin\git.exe" push -u origin main
& "C:\Program Files\Git\bin\git.exe" push origin v1.0.0
```

## 5. GitHub Release

Crie release a partir da tag `v1.0.0` e copie o conteúdo de [RELEASE_v1.0.0.md](./RELEASE_v1.0.0.md).

---

**Nota:** Git deve estar no PATH ou use o caminho completo `C:\Program Files\Git\bin\git.exe` como acima.
