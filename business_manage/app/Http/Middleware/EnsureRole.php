<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Bạn cần đăng nhập để truy cập.');
        }

        if (empty($roles)) {
            abort(403, 'Thiếu cấu hình role bảo vệ route.');
        }

        $currentRoleId = $this->currentWorkspaceRoleId($user);

        if (!$currentRoleId) {
            abort(403, 'Không xác định được quyền hiện tại của người dùng.');
        }

        $allowedRoleIds = $this->resolveAllowedRoleIds($roles);

        if (!in_array($currentRoleId, $allowedRoleIds, true)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }

    private function currentWorkspaceRoleId($user): ?int
    {
        $workspaceId = $user->current_workspace ?? null;

        if (!$workspaceId) {
            return null;
        }

        $roleId = DB::table('user_workspaces')
            ->where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->value('roleid');

        return $roleId !== null ? (int) $roleId : null;
    }

    private function resolveAllowedRoleIds(array $roles): array
    {
        $allowed = [];

        foreach ($roles as $role) {
            $role = trim((string) $role);

            if ($role === '') {
                continue;
            }

            if (is_numeric($role)) {
                $allowed[] = (int) $role;
                continue;
            }

            switch (strtolower($role)) {
                case 'admin':
                    $allowed[] = 1;
                    $allowed[] = 2;
                    break;
                case 'spadmin':
                case 'superadmin':
                    $allowed[] = 1;
                    break;
                case 'issale':
                case 'sale':
                    $allowed[] = 4;
                    break;
            }
        }

        return array_values(array_unique($allowed));
    }
}
