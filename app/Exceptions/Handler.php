<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Models\ErrorLog;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            try {
                $module = 'N/A';
                $operation = 'view';
                
                $route = request()->route();
                if ($route) {
                    // Determine module/section
                    $action = $route->getActionName();
                    if ($action && $action !== 'Closure') {
                        $controller = class_basename(explode('@', $action)[0]);
                        $module = str_replace('Controller', '', $controller);
                    } else {
                        $module = $route->getName() ?? 'N/A';
                    }

                    // Determine operation (add, update, delete)
                    $routeName = strtolower($route->getName() ?? '');
                    if (str_contains($routeName, 'store') || str_contains($routeName, 'create') || str_contains($routeName, 'add')) {
                        $operation = 'add';
                    } elseif (str_contains($routeName, 'update') || str_contains($routeName, 'edit')) {
                        $operation = 'update';
                    } elseif (str_contains($routeName, 'delete') || str_contains($routeName, 'destroy')) {
                        $operation = 'delete';
                    } else {
                        // Fallback using request method
                        $method = request()->method();
                        if ($method === 'POST') {
                            $operation = 'add';
                        } elseif ($method === 'PUT' || $method === 'PATCH') {
                            $operation = 'update';
                        } elseif ($method === 'DELETE') {
                            $operation = 'delete';
                        }
                    }
                }

                // Format a highly readable message
                $message = class_basename($e) . ': ' . $e->getMessage();
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $cleanDbError = $e->errorInfo[2] ?? $e->getMessage();
                    $message = "Database Error: " . $cleanDbError . " [SQL: " . $e->getSql() . "]";
                }

                // Find the first non-vendor file in the stack trace
                $file = $e->getFile();
                $lineNumber = $e->getLine();

                foreach ($e->getTrace() as $step) {
                    if (isset($step['file'])) {
                        $traceFile = $step['file'];
                        if (!str_contains($traceFile, 'vendor') && !str_contains($traceFile, 'bootstrap')) {
                            $file = $traceFile;
                            $lineNumber = $step['line'] ?? $lineNumber;
                            break;
                        }
                    }
                }

                // Make path relative to project root
                $relativeFile = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);

                ErrorLog::create([
                    'user_id'     => auth()->check() ? auth()->id() : null,
                    'operation'   => $operation,
                    'section'     => $module,
                    'message'     => $message,
                    'code'        => $e->getCode(),
                    'file'        => $relativeFile,
                    'line_number' => $lineNumber,
                    'url'         => request()->fullUrl(),
                    'created_at'  => \Carbon\Carbon::now('Asia/Kolkata'),
                ]);
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error("Failed to write to error_logs: " . $ex->getMessage());
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            if (auth()->check()) {
                return redirect('/');
            }
            return redirect('/login');
        }

        return parent::render($request, $e);
    }
}