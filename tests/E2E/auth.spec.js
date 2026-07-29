import { expect, test } from '@playwright/test';

test('usuário consegue entrar, navegar e sair', async ({ page }, testInfo) => {
    await page.goto('/login');
    await expect(page.getByRole('heading', { name: 'Bem-vindo de volta' })).toBeVisible();

    await page.getByLabel('E-mail').fill('demo@financi.ai.local');
    await page.getByLabel('Senha').fill('password');
    await page.getByLabel('Lembrar de mim').check();
    await page.getByRole('button', { name: 'Entrar' }).click();

    await expect(page).toHaveURL(/dashboard/);

    if (testInfo.project.name === 'mobile') {
        await page.getByRole('button', { name: 'Conversas' }).click();
    }

    await expect(page.getByRole('button', { name: 'Sair' })).toBeVisible();
    await page.getByRole('button', { name: 'Sair' }).click();
    await expect(page).toHaveURL('/');
});

test('recuperação de senha e cadastro são acessíveis', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('link', { name: 'Esqueci a senha' }).click();
    await expect(page).toHaveURL(/forgot-password/);
    await expect(page.getByLabel('E-mail')).toBeVisible();

    await page.goto('/register');
    await expect(page.getByRole('heading', { name: 'Crie sua conta' })).toBeVisible();
});

test('manifesto PWA está publicado', async ({ request }) => {
    const response = await request.get('/manifest.webmanifest');
    expect(response.ok()).toBeTruthy();
    expect((await response.json()).short_name).toBe('financi.ai');
});
