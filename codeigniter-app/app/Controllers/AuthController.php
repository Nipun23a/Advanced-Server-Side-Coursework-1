<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Helpers\EmailSendingHelper;
use App\Models\EmailVerificationModel;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;


class AuthController extends BaseController
{
    protected $userModel;
    protected $emailTokenModel;
    protected $resetTokenModel;
    protected $emailHelper;
    protected $session;

    public function __construct()
    {
        $this -> userModel = new UserModel();
        $this -> emailTokenModel = new EmailVerificationModel();
        $this -> resetTokenModel = new PasswordResetTokenModel();
        $this -> emailHelper = new EmailSendingHelper();
        $this -> session = session();

    }

    public function showRegister(){
        if ($this -> session -> get('user_id')){
            return redirect() -> to('/dashboard');
        }
        return view('auth/register',[
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function register() {
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Email Address is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique' => 'This email address is already registered.'
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|max_length[128]',
                'errors' => [
                    'required' => 'Password is required.',
                    'min_length' => 'Password must be at least 8 characters long.',
                ],
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Please confirm your password.',
                    'matches' => 'Passwords do not match.'
                ]
            ],
        ];
        if (! $this -> validate($rules)){
            return redirect() -> back() -> withInput()
                -> with('validation',$this->validator);
        }

        $email = $this -> request -> getPost('email');
        $password = $this -> request -> getPost('password');

        $allowedDomains = [
            'eastminster.ac.uk',
            'student.eastminster.ac.uk',
            'alumni.eastminster.ac.uk'
        ];

        $emailDomain = strtolower(substr($email, strpos($email, '@') + 1));
        if (!in_array($emailDomain, $allowedDomains)){
            return redirect() -> back() -> withInput()
                -> with('error', 'Registration is restricted to University of Eastminster email addresses.');
        }

        $passwordErrors = $this -> validatePasswordStength($password);
        if (!empty($passwordErrors)){
            return redirect() -> back() -> withInput()
                -> with('error',implode(' ',$passwordErrors));
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userId = $this -> userModel->insert([
            'email' => strtolower(trim($email)),
            'password_hash' => $passwordHash,
            'is_email_verified' => false,
            'role' => 'alumni'
        ]);

        if (! $userId){
            return redirect() -> back() -> withInput()
                -> with('error', 'Registration failed. Please try again later.');
        }

        $rawToken = $this -> emailTokenModel -> createToken($userId);
        $this -> emailHelper ->  sendVerificationEmail($email,$rawToken);

        return redirect() -> to('/auth/verify-notice')
            -> with ('success', 'Registration successful! Please check your email to verify your account.');
    }

    public function validatePasswordStength(string $password) : array
    {
        $errors = [];
        if (strlen($password) < 8){
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)){
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)){
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)){
            $errors[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)){
            $errors[] = 'Password must contain at least one special character.';
        }
        if (! preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        return $errors;
    }

    public function showVerifyNotice()
    {
        return view('auth/verify_notice');
    }

    public function verifyEmail()
    {
        $rawToken = $this -> request -> getGet('token');
        if (empty($rawToken)){
            return redirect() -> to('/auth/login')
                -> with('error', 'Invalid verification link.');
        }

        $tokenRecord = $this -> emailTokenModel -> validateToken($rawToken);
        if (! $tokenRecord){
            return redirect() -> to('/auth/login')
                -> with('error', 'Verification link is invalid or has expired.Please request a new verification email.');
        }
        $this -> userModel -> markEmailVerified($tokenRecord['user_id']);
        $this -> emailTokenModel -> markAsUsed($tokenRecord['id']);
        $this -> resetTokenModel -> invalidateUserToken($tokenRecord['user_id']);
        return redirect() -> to('/auth/login')
            -> with('success', 'Email verified successfully ! You can now log in.');

    }

    public function resendVerification()
    {
        $email = $this -> request -> getPost('email');
        if (empty($email)){
            return redirect() -> back() -> with('error','Please enter your email address.');
        }
        $user = $this -> userModel -> findByEmail($email);
        if (! $user){
            return redirect() -> back() -> with('success','If an account exists with this email address, a verification email has been sent');
        }
        if ($user['is_email_verified']){
            return redirect() -> back() -> with('success','Your email is already verified. Please log in. ');
        }

        $this -> emailTokenModel -> invalidateUserToken($user['id']);
        $rawToken = $this-> emailTokenModel->createToken($user['id']);
        $this-> emailHelper -> sendVerificationEmail($user['email'],$rawToken);
        return redirect() -> back() -> with('success', 'If an account exists, a new verification email has been sent.');
    }

    public function showLogin(){
        if ($this->session->get('user_id')){
            return redirect()->to('/dashboard');
        }
        return view('auth/login',[
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function login()
    {
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
            ],
        ];
        if (! $this -> validate($rules)){
            return redirect() -> back()
                -> withInput()
                -> with('validation',$this->validator);
        }
        $email = $this -> request -> getPost('email');
        $password = $this -> request -> getPost('password');

        $user = $this -> userModel -> findByEmail(strtolower(trim($email)));
        if (! $user || !password_verify($password, $user['password_hash'])){
            return redirect()->back() -> withInput() -> with('error','Invalid email or password.');
        }

        if (!$user['is_email_verified']){
            return redirect() -> back() -> withInput() -> with('error','Please verify your email address to access before logging in.')
                ->with('error','Please verify your email address to access before logging in.')
                -> with('show_resend',true);
        }

        $this -> session -> regenerate();

        $this->session->set([
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_role' => $user['role'],
            'is_logged_in' => true,
            'login_time' => time()
        ]);

        return redirect()->to('/dashboard')->with('success','Logged in successfully.');
    }

    public function logout()
    {
        $this -> session -> destroy();
        return redirect() -> to('/auth/login')
            -> with('success','You have been logged out successfully.');
    }

    public function showForgotPassword()
    {
        return view ('auth/forgot-password');
    }

    public function forgotPassword()
    {
        $rules = [
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email',
            ],
        ];

        if (! $this -> validate($rules)){
            return redirect() -> back()
                -> withInput()
                -> with('validation',$this->validator);
        }

        $email = $this -> request -> getPost('email');
        $user = $this -> userModel -> findByEmail(strtolower(trim($email)));

        if ($user){
            $rawToken = $this -> resetTokenModel -> createToken($user['id']);
            $this -> emailHelper -> sendPasswordResetEmail($email,$rawToken);
        }

        return redirect() -> back() -> with('success','If an account exists with that email exists, a password reset link has been sent.');
    }

    public function showResetPassword()
    {
        $rawToken = $this -> request -> getGet('token');
        if (empty($rawToken)){
            return redirect() -> to('/auth/forgot-password')
                ->with('error','Password reset link is invalid or has expired. Please request a new password reset link.');
        }

        return view('auth/reset-password',[
            'token' => $rawToken
        ]);
    }

    public function resetPassword()
    {
        $rules = [
            'token' => [
                'label' => 'Token',
                'rules' => 'required',
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|max_length[128]',
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
            ],
        ];
        if (! $this -> validate($rules)){
            return redirect() -> back()
                -> withInput()
                -> with('validation',$this->validator);
        }

        $rawToken = $this -> request -> getPost('token');
        $password = $this->request->getPost('password');

        $passwordErrors = $this->validatePasswordStength($password);
        if (! empty($passwordErrors)){
            return redirect() -> back() -> withInput() -> with('error',implode(' ',$passwordErrors));
        }

        $tokenRecord = $this -> resetTokenModel -> validateUserTokens($rawToken);
        if (! $tokenRecord){
            return redirect() -> to('/auth/forgot-password')
                ->with('error','Password reset link is invalid or has expired. Please request a new password reset link.');
        }

        $newPasswordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $this-> userModel -> updatePassword($tokenRecord['user_id'],$newPasswordHash);

        return redirect() -> to('/auth/login') -> with('success','Password reset successful. Please log in with your new password.');
    }

}


