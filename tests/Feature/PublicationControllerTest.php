<?php

namespace Tests\Feature;

use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_publication_with_existing_cache()
    {
        // Create a mock publication in the database
        $publication = Publication::create([
            'doi' => '10.1038/nphys1170',
            'data' => ['title' => 'Measured measurement']
        ]);

        // Make a request to the API endpoint
        $response = $this->get('/publications?doi=10.1038/nphys1170');

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'source' => 'cache',
            'data' => [['title' => 'Measured measurement']],
        ]);
    }

    public function test_pull_record_from_crossref_if_not_in_cache()
    {
        // Mock the HTTP response from CrossRef
        Http::fake([
            'https://api.crossref.org/works/10.1038/nphys1170' => Http::response([
                'status' => 'ok',
                'message' => [
                    'title' => ['Measured measurement'],
                ]
            ], 200),
        ]);

        // Make a request to the API endpoint with a DOI that's not in cache
        $response = $this->get('/publications?doi=10.1038/nphys1170');

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'source' => 'crossref',
            'data' => [
                'status' => 'ok',
                'message' => [
                    'title' => ['Measured measurement'],
                ],
            ],
        ]);

        // Assert the publication is stored in the database
        $this->assertDatabaseHas('publications', [
            'doi' => '10.1038/nphys1170',
            'data' => json_encode([
                'status' => 'ok',
                'message' => [
                    'title' => ['Measured measurement'],
                ],
            ]),
        ]);
    }

    public function test_get_publication_not_found()
    {
        // Mock the HTTP response from CrossRef
        Http::fake([
            'https://api.crossref.org/works/10.1038/nonexistentdoi' => Http::response([], 404),
        ]);

        // Make a request to the API endpoint
        $response = $this->get('/publications?doi=10.1038/nonexistentdoi');

        // Assert the response
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Publication not found',
        ]);
    }

    public function test_get_publication_with_partial_doi_in_cache()
    {
        // Create mock publications in the database
        $publication1 = Publication::create([
            'doi' => '10.1038/nphys1170',
            'data' => ['title' => 'Measured measurement']
        ]);

        $publication2 = Publication::create([
            'doi' => '10.1038/nphys1171',
            'data' => ['title' => 'Another measurement']
        ]);

        // Make a request to the API endpoint with a partial DOI
        $response = $this->get('/publications?doi=10.1038/nphys');

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'source' => 'cache',
            'data' => [$publication1->data, $publication2->data],
        ]);
    }
}
