# RG1.1 — Release Tag Report

## Objective

Document the official Version 1.0.0 release tag process.

## Current Tag State

| Tag | Local | Remote | Status |
|-----|-------|--------|--------|
| `v1.0.0` | Absent | Absent | No cleanup required |

## Official Release Workflow

After RG1 go-live certification is approved, execute the following on the `master` branch:

```bash
# Ensure you are on master and it contains develop
git checkout master
git merge --no-ff develop
git push origin master

# Create and push the official release tag
git tag -a v1.0.0 -m "VESTRA Commerce Platform Version 1.0.0"
git push origin v1.0.0
```

## Tag Details

| Property | Value |
|----------|-------|
| Tag name | `v1.0.0` |
| Tag message | `VESTRA Commerce Platform Version 1.0.0` |
| Target branch | `master` |
| Target commit | `[TO BE FILLED AFTER MERGE]` |

## Verification

```bash
git tag -n1 v1.0.0
git ls-remote --tags origin
```

Expected:

```
v1.0.0            VESTRA Commerce Platform Version 1.0.0
```

## Future Development Branch

After tagging `v1.0.0`, development of Stage 18.6 should continue on `develop`:

```bash
git checkout develop
# Begin Stage 18.6 — Customer Account, Profile & Self-Service Portal
```

## Conclusion

- [ ] Release tag process documented.
- [ ] Tag target commit identified.
- [ ] Future development branch identified.
