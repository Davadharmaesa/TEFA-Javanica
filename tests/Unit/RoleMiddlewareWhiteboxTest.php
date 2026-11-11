<?php

namespace Tests\Unit;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleMiddlewareWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * TC-WB-RBAC-001 (Path 1: Decision !auth()->check() == true)
     * Menguji jika user belum login, request diarahkan (redirect) ke root '/'.
     */
    public function test_tc_wb_rbac_001_unauthenticated_user_redirects_to_root(): void
    {
        $middleware = new RoleMiddleware();
        $request = Request::create('/admin/dashboard', 'GET');

        // Pastikan tidak ada user terautentikasi
        auth()->logout();

        $response = $middleware->handle($request, function ($req) {
            return response('Should not reach here');
        }, 'Admin');

        $this->assertTrue($response->isRedirect(url('/')));
    }

    /**
     * TC-WB-RBAC-002 (Path 2: Decision !in_array($userRole, $roles) == true)
     * Menguji jika user terautentikasi tapi role tidak ada dalam daftar yang diizinkan,
     * melempar HttpException 403 ('Akses ditolak').
     */
    public function test_tc_wb_rbac_002_unauthorized_role_throws_403_abort(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Operator',
            'email' => 'operator_wb@tefa.com',
            'role' => 'operator',
        ]);

        $this->actingAs($user);

        $middleware = new RoleMiddleware();
        $request = Request::create('/users', 'GET');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Akses ditolak');

        try {
            $middleware->handle($request, function ($req) {
                return response('Should not reach here');
            }, 'Admin', 'PIC');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            throw $e;
        }
    }

    /**
     * TC-WB-RBAC-003 (Path 3: Role sesuai, next($request) dieksekusi)
     * Menguji jika user memiliki role yang sesuai (misal: 'Admin' atau 'PIC'),
     * request diteruskan ke penanganan berikutnya dan menghasilkan response 200 OK.
     */
    public function test_tc_wb_rbac_003_authorized_role_passes_middleware(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin_wb@tefa.com',
            'role' => 'Admin', // case-insensitive test
        ]);

        $this->actingAs($user);

        $middleware = new RoleMiddleware();
        $request = Request::create('/users', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('Access Granted', 200);
        }, 'admin', 'pic');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Access Granted', $response->getContent());
    }
}

