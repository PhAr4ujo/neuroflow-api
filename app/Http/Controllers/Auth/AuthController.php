<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationEmailRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\MessageResource;
use App\Http\Resources\Auth\PayloadResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Interfaces\IUserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly IUserService $userService,
    ) {}

    public function register(RegisterRequest $request): PayloadResource
    {
        $user = $this->userService->register($request->validated());

        return new PayloadResource([
            'message' => 'Registration successful. Please verify your email address.',
            'data' => UserResource::make($user),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): LoginResource
    {
        return new LoginResource(array_merge(
            ['message' => 'Login successful.'],
            $this->userService->login($request->validated())
        ));
    }

    public function resendVerificationEmail(ResendVerificationEmailRequest $request): MessageResource
    {
        $this->userService->resendEmailVerification($request->validated('email'));

        return new MessageResource([
            'message' => 'Verification email sent successfully.',
        ]);
    }

    public function verifyEmail(int $id, string $hash): PayloadResource
    {
        $user = $this->userService->verifyEmail($id, $hash);

        return new PayloadResource([
            'message' => 'Email verified successfully.',
            'data' => UserResource::make($user),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): MessageResource
    {
        $this->userService->sendPasswordResetLink($request->validated('email'));

        return new MessageResource([
            'message' => 'Password reset link sent successfully.',
        ]);
    }

    public function showResetPassword(Request $request, string $token): PayloadResource
    {
        return new PayloadResource([
            'message' => 'Use this token and email address to complete the password reset.',
            'data' => [
                'token' => $token,
                'email' => $request->query('email'),
            ],
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): MessageResource
    {
        $this->userService->resetPassword($request->validated());

        return new MessageResource([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function logout(Request $request): MessageResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->userService->logout($user);

        return new MessageResource([
            'message' => 'Logout successful.',
        ]);
    }
}
