# Git e release v1.0.0

O workspace **não tinha repositório Git** na verificação de 2026-05-26. Use este guia para versionar e publicar a tag.

## 1. Inicializar repositório (raiz do monorepo)

```powershell
cd c:\Projetos\ProjetoChatbot
& "C:\Program Files\Git\bin\git.exe" init
& "C:\Program Files\Git\bin\git.exe" add .
& "C:\Program Files\Git\bin\git.exe" status
```

Confirme que `.env`, `vendor/`, `node_modules/` estão no `.gitignore` antes de commitar.

## 2. Primeiro commit (release 1.0.0)

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
