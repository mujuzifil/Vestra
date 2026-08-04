# Phase 13.12 — Permissions

`App\Policies\DistributorBranchPolicy` gates all access to the Territories workspace and the underlying branch records.

## Policy Matrix

| Ability | Admin | Distributor (owns branch) |
|---------|-------|----------------------------|
| `viewAny` | ✅ | ❌ |
| `view` | ✅ | ✅ (own branches only) |
| `update` | ✅ | ✅ (own branches only) |
| `delete` | ✅ | ✅ (own branches only) |
| `export` | ✅ | ❌ |

`create` is intentionally **not** defined on the policy in this phase. `TerritoriesPage::canCreateBranch()` calls `Gate::allows('create', DistributorBranch::class)`, which safely resolves to `false` while the ability is undefined — so the workspace omits any "Add Branch" call-to-action until a `create` ability is explicitly authored and wired up. This satisfies the "no Add unless create is authorized and exists" requirement without guessing at future authorization rules.

## Notes

- `TerritoriesPage::mount()` calls `Gate::authorize('viewAny', DistributorBranch::class)`.
- `openDetailDrawer()` authorises `view` on the specific branch before loading detail data.
- The export route (`TerritoryExportController`) authorises `export` before streaming any file.
- `DistributorBranchPolicy` is already registered against `App\Models\DistributorBranch` in `AuthServiceProvider::$policies` — no provider changes were required for this phase.
