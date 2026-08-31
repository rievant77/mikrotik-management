<?php

namespace Tests\Feature;

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAndBrandingTest extends TestCase
{
    public function test_profile_page_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->get(route('settings.profile'));
        $res->assertStatus(200);
        $res->assertSee('Pengaturan Profil');
        $res->assertSee('Informasi Akun Administrator');
        $res->assertSee('Ganti Kata Sandi');
        $res->assertSee('Identitas');
    }

    public function test_update_profile_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $this->actingAs($user);

        $res = $this->putJson(route('settings.profile.update'), [
            'name' => 'New Superadmin',
            'email' => 'newadmin@example.com',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals('New Superadmin', $user->name);
        $this->assertEquals('newadmin@example.com', $user->email);
    }

    public function test_update_password_validation_and_success(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123'),
        ]);
        $this->actingAs($user);

        // 1. Wrong current password fails
        $badRes = $this->putJson(route('settings.profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $badRes->assertStatus(422);

        // 2. Correct current password succeeds
        $res = $this->putJson(route('settings.profile.password'), [
            'current_password' => 'secret123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_update_app_branding_and_logo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $setting = RouterSetting::create([
            'name' => 'Core Router',
            'host' => '192.168.88.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => '',
            'app_name' => 'Default Name',
        ]);

        $res = $this->postJson(route('settings.profile.branding'), [
            'app_name' => 'SuperNet WiFi Billing',
            'tagline' => 'Solusi Internet Cepat',
            'contact_phone' => '08123456789',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $setting->refresh();
        $this->assertEquals('SuperNet WiFi Billing', $setting->app_name);
        $this->assertEquals('Solusi Internet Cepat', $setting->tagline);
        $this->assertEquals('08123456789', $setting->contact_phone);
    }
}
