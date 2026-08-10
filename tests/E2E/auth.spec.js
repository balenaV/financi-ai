import { expect, test } from '@playwright/test';

test('usuário consegue entrar, navegar e sair', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('heading', { name: 'Bem-vindo de volta' })).toBeVisible();

    const loginForm = page.locator('.auth-form--login');
    await loginForm.locator('input[name="email"]').fill('demo@financi.ai.local');
    await loginForm.locator('input[name="password"]').fill('password');
    await loginForm.getByLabel('Manter conectado neste dispositivo').check();
    await loginForm.getByRole('button', { name: 'Entrar na minha conta' }).click();

    await expect(page).toHaveURL(/dashboard/);
    await page.getByRole('button', { name: 'Sua conta', exact: true }).click();
    // Sem "exact": o ícone do botão (sem aria-hidden, herdado 1:1 do pacote de
    // design) entra no nome acessível calculado pelo Chromium junto do texto.
    await expect(page.getByRole('button', { name: 'Sair da conta' })).toBeVisible();
    await page.getByRole('button', { name: 'Sair da conta' }).click();
    await expect(page).toHaveURL('/');
});

test('recuperação de senha e cadastro são acessíveis', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('link', { name: 'Esqueci minha senha' }).click();
    await expect(page).toHaveURL(/forgot-password/);
    await expect(page.getByLabel('E-mail')).toBeVisible();

    await page.goto('/register');
    await expect(page.getByRole('heading', { name: 'Comece a organizar hoje' })).toBeVisible();
});

test('manifesto PWA está publicado', async ({ request }) => {
    const response = await request.get('/manifest.webmanifest');
    expect(response.ok()).toBeTruthy();
    expect((await response.json()).short_name).toBe('financi.ai');
});
