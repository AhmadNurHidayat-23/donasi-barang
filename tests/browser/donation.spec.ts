import { test, expect, type Page } from "@playwright/test";
async function register(p: Page, name: string, email: string) {
    await p.goto("/masuk?mode=daftar");
    await p.getByLabel("Nama lengkap").fill(name);
    await p.getByLabel("Email", { exact: true }).fill(email);
    await p
        .getByLabel("Kata sandi", { exact: true })
        .fill(process.env.E2E_PASSWORD!);
    await p.getByLabel("Ulangi kata sandi").fill(process.env.E2E_PASSWORD!);
    await p.getByRole("button", { name: "Daftar anggota" }).click();
    await expect(p).toHaveURL(/riwayat/);
}
async function noOverflow(p: Page) {
    expect(
        await p.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth + 1,
        ),
    ).toBe(true);
}
test("Posting, pencarian, pengajuan privat, pemilihan, dan konfirmasi dua pihak", async ({
    page,
    browser,
}) => {
    const errors: string[] = [];
    page.on("pageerror", (e) => errors.push(e.message));
    await register(page, "Donatur Browser", "donatur@example.test");
    await page.getByRole("link", { name: "Bagikan barang baru" }).click();
    await page.getByLabel("Nama barang").fill("Buku Cerita Anak");
    await page
        .getByLabel("Deskripsi & kekurangan")
        .fill(
            "Lima buku cerita anak bersih, halaman lengkap, cocok untuk belajar membaca.",
        );
    await page.getByLabel("Kategori", { exact: false }).selectOption("Buku");
    await page.getByLabel("Kondisi barang").selectOption("Baik");
    await page.getByLabel("Kota (informasi umum)").selectOption("Bandung");
    await page
        .getByLabel("Foto barang")
        .setInputFiles("public/images/giving.jpg");
    await page.getByRole("button", { name: "Publikasikan donasi" }).click();
    await expect(page).toHaveURL(/donasi\/[a-f0-9-]+$/);
    const item = new URL(page.url()).pathname;
    const context = await browser.newContext();
    const recipient = await context.newPage();
    recipient.on("pageerror", (e) => errors.push(e.message));
    await register(recipient, "Penerima Browser", "penerima@example.test");
    await recipient.goto(item);
    await recipient
        .getByLabel("Ceritakan kebutuhan Anda")
        .fill(
            "Buku ini akan dipakai pada kegiatan membaca anak di komunitas belajar.",
        );
    await recipient
        .getByRole("button", { name: "Ajukan penerimaan barang" })
        .click();
    await expect(recipient.locator("body")).toContainText(
        "Status pengajuan Anda",
    );
    await page.reload();
    await page.getByRole("button", { name: "Pilih penerima" }).click();
    await expect(page.locator(".item-info .badge")).toHaveText("Dicadangkan");
    await page
        .getByLabel("Petunjuk pengambilan privat")
        .fill(
            "Temui pengurus di ruang komunitas pada Sabtu pukul 10 pagi. Kode pertemuan: RAHASIA.",
        );
    await page.getByRole("button", { name: "Simpan petunjuk privat" }).click();
    const guestContext = await browser.newContext();
    const guest = await guestContext.newPage();
    await guest.goto(item);
    await expect(guest.locator("body")).not.toContainText("RAHASIA");
    await expect(guest.locator("body")).not.toContainText("Donatur Browser");
    await expect(guest.locator("body")).not.toContainText(
        "penerima@example.test",
    );
    await recipient.reload();
    await expect(recipient.locator(".pickup-note")).toContainText("RAHASIA");
    await page
        .getByRole("button", { name: "Saya sudah menyerahkan barang" })
        .click();
    await expect(page.locator(".item-info .badge")).toHaveText("Dicadangkan");
    await recipient.reload();
    await recipient
        .getByRole("button", { name: "Saya sudah menerima barang" })
        .click();
    await expect(recipient.locator(".item-info .badge")).toHaveText(
        "Selesai diserahterimakan",
    );
    await page.reload();
    await expect(page.locator(".item-info .badge")).toHaveText(
        "Selesai diserahterimakan",
    );
    await page.getByRole("link", { name: "Riwayat saya" }).click();
    await expect(page.locator(".history-list").first()).toContainText(
        "Buku Cerita Anak",
    );
    for (const width of [1440, 768, 390]) {
        await recipient.setViewportSize({ width, height: 900 });
        await recipient.goto(item);
        await recipient
            .locator(".item-photo img")
            .evaluate(async (img: HTMLImageElement) => img.decode());
        await noOverflow(recipient);
        await recipient.screenshot({
            path: "test-results/handover-" + width + ".png",
            fullPage: true,
        });
        await guest.setViewportSize({ width, height: 900 });
        await guest.goto("/");
        await noOverflow(guest);
        await guest.screenshot({
            path: "test-results/home-" + width + ".png",
            fullPage: true,
        });
    }
    expect(errors).toEqual([]);
    await guestContext.close();
    await context.close();
});
