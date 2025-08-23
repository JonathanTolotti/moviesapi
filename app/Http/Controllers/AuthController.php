<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCredentialsException;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $this->authService->register($request->validated());

        return response()->json($data, Response::HTTP_CREATED);
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());

            return response()->json($data);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($request->user());

        return response()->noContent();
    }
}
