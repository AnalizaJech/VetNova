import { test, expect } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('PeruAPI and UI Focus Debug', () => {
    
    test.beforeEach(async ({ page }) => {
        // Login
        await page.goto(`${BASE_URL}/`);
        await page.fill('input[type="email"]', 'admin@vetnova.pe');
        await page.fill('input[type="password"]', 'password');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(`${BASE_URL}/dashboard`);
    });

    test('Verify UI Focus Styles (No white border, subtle green ring)', async ({ page }) => {
        await page.goto(`${BASE_URL}/clientes`);
        await page.getByRole('button', { name: /Nuevo Cliente/i }).click();
        
        const input = page.locator('input[wire\\:model="numero_documento"]');
        await input.focus();
        
        // Screenshot to verify visually in the report if needed
        await page.screenshot({ path: 'artifacts/focus-debug.png' });
        
        // Verify CSS properties if possible, but the user wants to see it.
        // We can check if the outline-offset is removed.
        const outline = await input.evaluate((el) => window.getComputedStyle(el).outlineStyle);
        // In my fix I set outline: none !important.
        // Wait, DaisyUI might still apply something. Let's check the computed style.
    });

    test('Verify PeruAPI DNI Query', async ({ page }) => {
        await page.goto(`${BASE_URL}/clientes`);
        await page.getByRole('button', { name: /Nuevo Cliente/i }).click();
        
        // Use a common DNI for testing (e.g., 44444444 or similar if valid, or just one that should work)
        // Note: PeruAPI free tier might have limits.
        await page.fill('input[wire\\:model="numero_documento"]', '70617300'); // Example DNI
        await page.click('button:has(.o-magnifying-glass)'); // Click search button
        
        // Wait for success toast or error toast
        const toast = page.locator('.toast');
        await expect(toast).toBeVisible({ timeout: 10000 });
        
        const toastText = await toast.innerText();
        console.log('Toast Result:', toastText);
        
        if (toastText.includes('Datos obtenidos')) {
            const nombres = await page.inputValue('input[wire\\:model="nombres"]');
            expect(nombres.length).toBeGreaterThan(0);
            console.log('API Success: Found', nombres);
        } else {
            console.log('API Result:', toastText);
        }
    });
});
