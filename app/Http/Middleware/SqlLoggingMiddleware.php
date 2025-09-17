<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use PDOException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\ForeignKeyConstraintViolationException;

class SqlLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            return $response;
        } catch (QueryException $e) {
            \Log::info('SqlLoggingMiddleware: QueryException caught');
            $this->logSqlError($e, $request, 'Query Exception');
            throw $e;
        } catch (UniqueConstraintViolationException $e) {
            \Log::info('SqlLoggingMiddleware: UniqueConstraintViolationException caught');
            $this->logSqlError($e, $request, 'Unique Constraint Violation');
            throw $e;
        } catch (ForeignKeyConstraintViolationException $e) {
            \Log::info('SqlLoggingMiddleware: ForeignKeyConstraintViolationException caught');
            $this->logSqlError($e, $request, 'Foreign Key Constraint Violation');
            throw $e;
        } catch (PDOException $e) {
            \Log::info('SqlLoggingMiddleware: PDOException caught');
            $this->logSqlError($e, $request, 'PDO Exception');
            throw $e;
        } catch (\Exception $e) {
            \Log::info('SqlLoggingMiddleware: Generic Exception caught', [
                'exception_type' => get_class($e),
                'message' => $e->getMessage()
            ]);

            // Solo log si es un error relacionado con SQL
            if ($this->isSqlRelatedError($e)) {
                \Log::info('SqlLoggingMiddleware: SQL related error detected');
                $this->logSqlError($e, $request, 'SQL Related Error');
            }
            throw $e;
        }
    }

    /**
     * Log SQL specific errors with detailed information
     */
    private function logSqlError(\Exception $e, Request $request, string $errorType): void
    {
        $logData = [
            'error_type' => $errorType,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user() ? auth()->user()->name : 'guest',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ];

        // Agregar información específica de SQL si está disponible
        if ($e instanceof QueryException) {
            $logData['sql'] = $e->getSql();
            $logData['bindings'] = $e->getBindings();
        }

        if ($e instanceof PDOException && method_exists($e, 'getSQLState')) {
            $logData['sql_state'] = $e->getSQLState();
        }

        // Log como ERROR para errores SQL
        Log::error('SQL Error Detected', $logData);
    }

    /**
     * Check if the error is SQL related by analyzing the error message
     */
    private function isSqlRelatedError(\Exception $e): bool
    {
        $errorMessage = strtolower($e->getMessage());
        $sqlKeywords = [
            'sql',
            'database',
            'query',
            'syntax',
            'table',
            'column',
            'constraint',
            'foreign key',
            'primary key',
            'unique',
            'pdo',
            'sqlite',
            'mysql',
            'postgresql',
            'duplicate entry',
            'no such table',
            'no such column',
            'constraint violation',
            'data type mismatch',
            'cannot start a transaction',
            'database is locked'
        ];

        foreach ($sqlKeywords as $keyword) {
            if (strpos($errorMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
