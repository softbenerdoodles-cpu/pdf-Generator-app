<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\Templates\TemplateManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function sample(string $template = 'invoice'): array
    {
        return app(TemplateManager::class)->get($template)->sample();
    }

    #[Test]
    public function a_visitor_can_create_an_account(): void
    {
        $this->post('/register', [
            'name' => 'Sarah Johnson',
            'email' => 'sarah@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect('/documents');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sarah@example.com']);
    }

    #[Test]
    public function a_user_can_sign_in_and_out(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-passphrase')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-passphrase'])
            ->assertRedirect('/documents');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    #[Test]
    public function the_wrong_password_is_refused(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'not-it'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function saving_a_document_needs_an_account(): void
    {
        $this->post('/documents', ['template' => 'invoice', 'data' => $this->sample()])
            ->assertRedirect('/login');

        $this->assertDatabaseCount('documents', 0);
    }

    #[Test]
    public function a_signed_in_user_can_save_a_document_and_its_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/documents', ['template' => 'invoice', 'data' => $this->sample()])
            ->assertRedirect();

        $document = Document::sole();

        $this->assertSame($user->id, $document->user_id);
        $this->assertSame('Invoice INV-1001', $document->title);
        $this->assertGreaterThan(1000, $document->size);
        Storage::disk('local')->assertExists($document->path);
    }

    #[Test]
    public function a_saved_document_can_be_listed_viewed_and_downloaded(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', ['template' => 'invoice', 'data' => $this->sample()]);
        $document = Document::sole();

        $this->actingAs($user)->get('/documents')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Documents/Index')->has('documents.data', 1));

        $this->actingAs($user)->get("/documents/{$document->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Documents/Show')
                ->where('document.title', 'Invoice INV-1001')
            );

        $this->actingAs($user)->get("/documents/{$document->id}/download")
            ->assertOk()
            ->assertDownload('invoice-inv-1001.pdf');
    }

    #[Test]
    public function one_user_can_never_reach_another_users_document(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($owner)->post('/documents', ['template' => 'invoice', 'data' => $this->sample()]);
        $document = Document::sole();

        $this->actingAs($stranger)->get("/documents/{$document->id}")->assertNotFound();
        $this->actingAs($stranger)->get("/documents/{$document->id}/download")->assertNotFound();
        $this->actingAs($stranger)->delete("/documents/{$document->id}")->assertNotFound();

        $this->assertDatabaseCount('documents', 1);
    }

    #[Test]
    public function deleting_a_document_removes_its_file_too(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', ['template' => 'receipt', 'data' => $this->sample('receipt')]);
        $document = Document::sole();
        $path = $document->path;

        $this->actingAs($user)->delete("/documents/{$document->id}")->assertRedirect('/documents');

        $this->assertDatabaseCount('documents', 0);
        Storage::disk('local')->assertMissing($path);
    }

    #[Test]
    public function business_details_are_saved_once_and_prefilled_into_new_documents(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account/business', [
            'name' => 'Aneta Studio',
            'email' => 'hello@aneta.example',
            'phone' => '+1 (312) 555-0143',
            'address' => "456 Market St\nChicago, IL 60601",
        ])->assertRedirect();

        $this->actingAs($user)->get('/generate/invoice')
            ->assertInertia(fn ($page) => $page
                ->where('business.name', 'Aneta Studio')
                ->where('business.email', 'hello@aneta.example')
            );
    }

    #[Test]
    public function saving_business_details_twice_updates_rather_than_duplicates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account/business', ['name' => 'First Name']);
        $this->actingAs($user)->put('/account/business', ['name' => 'Second Name']);

        $this->assertDatabaseCount('business_profiles', 1);
        $this->assertDatabaseHas('business_profiles', ['user_id' => $user->id, 'name' => 'Second Name']);
    }

    #[Test]
    public function customers_are_saved_and_offered_in_the_editor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/account/customers', [
            'name' => 'Bright Ideas Co.',
            'email' => 'accounts@brightideas.example',
        ])->assertRedirect();

        $this->actingAs($user)->get('/generate/invoice')
            ->assertInertia(fn ($page) => $page
                ->has('customers', 1)
                ->where('customers.0.name', 'Bright Ideas Co.')
            );
    }

    #[Test]
    public function a_customer_belonging_to_someone_else_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $customer = $owner->customers()->create(['name' => 'Bright Ideas Co.']);

        $this->actingAs($stranger)->delete("/account/customers/{$customer->id}")->assertNotFound();

        $this->assertDatabaseCount('customers', 1);
    }

    #[Test]
    public function the_account_page_shows_what_has_been_saved(): void
    {
        $user = User::factory()->create();
        $user->businessProfile()->create(['name' => 'Aneta Studio']);
        $user->customers()->create(['name' => 'Bright Ideas Co.']);

        $this->actingAs($user)->get('/account')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account')
                ->where('business.name', 'Aneta Studio')
                ->has('customers', 1)
            );
    }
}
