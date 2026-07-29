<div class="auth-social-grid">
    <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="auth-social-button">
        <i class="fa-brands fa-google" aria-hidden="true"></i>
        <span>Google</span>
    </a>
    <a href="{{ route('social.redirect', ['provider' => 'github']) }}" class="auth-social-button">
        <i class="fa-brands fa-github" aria-hidden="true"></i>
        <span>GitHub</span>
    </a>
</div>
