<?php

use App\Filament\Resources\SearchQueries\Pages\EditSearchQuery;
use App\Filament\Resources\SearchQueries\Pages\ListSearchQueries;
use App\Filament\Resources\SearchQueries\RelationManagers\TermsRelationManager;
use App\Filament\Resources\SearchQueries\SearchQueryResource;
use App\Filament\Resources\Terms\TermResource;
use App\Models\SearchQuery;
use App\Models\Term;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

describe('List Search Queries Page', function () {

    it('renders search queries table with required columns', function () {
        livewire(ListSearchQueries::class)
            ->assertSuccessful()
            ->assertTableColumnExists('search_query')
            ->assertTableColumnExists('count')
            ->assertTableColumnExists('has_result')
            ->assertCanRenderTableColumn('search_query')
            ->assertCanRenderTableColumn('count')
            ->assertCanRenderTableColumn('has_result')
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn('title')
            ->assertCanRenderTableColumn('terms_count')
            ->assertCanRenderTableColumn('created_at')
            ->assertCanRenderTableColumn('updated_at');
    });

    it('filters search queries by result status', function () {
        $withResult = SearchQuery::query()->create([
            'search_query' => 'alpha query',
            'count' => 3,
            'has_result' => true,
        ]);
        $withoutResult = SearchQuery::query()->create([
            'search_query' => 'beta query',
            'count' => 1,
            'has_result' => false,
        ]);

        livewire(ListSearchQueries::class)
            ->assertTableFilterExists('has_result')
            ->filterTable('has_result', true)
            ->assertCanSeeTableRecords([$withResult])
            ->assertCanNotSeeTableRecords([$withoutResult])
            ->resetTableFilters()
            ->filterTable('has_result', false)
            ->assertCanSeeTableRecords([$withoutResult])
            ->assertCanNotSeeTableRecords([$withResult]);
    });

    it('can search search queries by search_query column', function () {
        $matching = SearchQuery::query()->create([
            'search_query' => 'javascript tutorial',
            'count' => 5,
            'has_result' => true,
        ]);
        $nonMatching = SearchQuery::query()->create([
            'search_query' => 'python guide',
            'count' => 2,
            'has_result' => false,
        ]);

        livewire(ListSearchQueries::class)
            ->searchTable('javascript')
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$nonMatching]);
    });

    it('links terms count to terms list filtered by search query', function () {
        $searchQuery = SearchQuery::query()->create([
            'search_query' => 'link me',
            'count' => 4,
            'has_result' => true,
        ]);

        $searchQuery->terms()->attach(Term::factory()->create());

        $termsIndexUrl = TermResource::getFilteredIndexUrl([
            'searchQueries' => [$searchQuery->getKey()],
        ]);

        livewire(ListSearchQueries::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$searchQuery])
            ->assertTableColumnStateSet('terms_count', 1, record: $searchQuery)
            ->assertSeeHtml('href="'.$termsIndexUrl.'"');
    });

    it('can bulk delete search queries', function () {
        $firstQuery = SearchQuery::query()->create([
            'search_query' => 'delete me 1',
            'count' => 1,
            'has_result' => false,
        ]);
        $secondQuery = SearchQuery::query()->create([
            'search_query' => 'delete me 2',
            'count' => 2,
            'has_result' => true,
        ]);
        $keepQuery = SearchQuery::query()->create([
            'search_query' => 'keep me',
            'count' => 3,
            'has_result' => true,
        ]);

        livewire(ListSearchQueries::class)
            ->callTableBulkAction(DeleteBulkAction::class, [$firstQuery, $secondQuery])
            ->assertNotified();

        assertDatabaseMissing('search_queries', ['id' => $firstQuery->id]);
        assertDatabaseMissing('search_queries', ['id' => $secondQuery->id]);
        assertDatabaseHas('search_queries', ['id' => $keepQuery->id]);
    });
});

describe('Create Search Query Restriction', function () {

    it('does not render the create action button on the list page', function () {
        livewire(ListSearchQueries::class)
            ->assertSuccessful()
            ->assertActionDoesNotExist(CreateAction::class);
    });

    it('does not have a create page registered in the resource', function () {
        $pages = SearchQueryResource::getPages();

        expect($pages)->not->toHaveKey('create');
    });

    it('returns 404 when trying to access the create page directly', function () {
        $user = User::factory()->create();

        $createUrl = SearchQueryResource::getUrl('index').'/create';

        $this->actingAs($user)
            ->get($createUrl)
            ->assertNotFound();
    });

    it('can filter records that have terms', function () {
        $queryWithTerms = SearchQuery::factory()
            ->has(Term::factory()->count(2))
            ->create();

        $queryWithoutTerms = SearchQuery::factory()->create();

        livewire(ListSearchQueries::class)
            ->assertCanSeeTableRecords([$queryWithTerms, $queryWithoutTerms])
            ->filterTable('has_terms', true)
            ->assertCanSeeTableRecords([$queryWithTerms])
            ->assertCanNotSeeTableRecords([$queryWithoutTerms]);
    });

    it('can filter records that have no terms', function () {
        $queryWithTerms = SearchQuery::factory()
            ->has(Term::factory()->count(2))
            ->create();

        $queryWithoutTerms = SearchQuery::factory()->create();

        livewire(ListSearchQueries::class)
            ->filterTable('has_terms', false)
            ->assertCanSeeTableRecords([$queryWithoutTerms])
            ->assertCanNotSeeTableRecords([$queryWithTerms]);
    });

    it('can reset the terms status filter', function () {
        $queryWithTerms = SearchQuery::factory()
            ->has(Term::factory()->count(2))
            ->create();

        $queryWithoutTerms = SearchQuery::factory()->create();

        livewire(ListSearchQueries::class)
            ->filterTable('has_terms', true)
            ->assertCanNotSeeTableRecords([$queryWithoutTerms])
            ->filterTable('has_terms', null)
            ->assertCanSeeTableRecords([$queryWithTerms, $queryWithoutTerms]);
    });

});

describe('Edit Search Query Page', function () {

    it('renders the edit page and loads data correctly with default null title', function () {
        $searchQuery = SearchQuery::query()->create([
            'search_query' => 'static query',
            'title' => null,
            'count' => 5,
            'has_result' => true,
        ]);

        livewire(EditSearchQuery::class, ['record' => $searchQuery->getKey()])
            ->assertSuccessful()
            ->assertSchemaStateSet([
                'title' => null,
                'search_query' => 'static query',
                'count' => 5,
                'has_result' => true,
            ])
            ->assertFormFieldExists('title')
            ->assertFormFieldEnabled('title')
            ->assertSchemaComponentExists('search_query')
            ->assertSchemaComponentExists('count')
            ->assertSchemaComponentExists('has_result');
    });

    it('can edit only the title of a search query', function () {
        $searchQuery = SearchQuery::query()->create([
            'search_query' => 'old query',
            'title' => null,
            'count' => 2,
            'has_result' => false,
        ]);

        livewire(EditSearchQuery::class, ['record' => $searchQuery->getKey()])
            ->fillForm([
                'title' => 'Updated title',
                'search_query' => 'hacked query',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas('search_queries', [
            'id' => $searchQuery->id,
            'title' => 'Updated title',
            'search_query' => 'old query',
        ]);
    });

    it('can clear the title of a search query (set to null)', function () {
        $searchQuery = SearchQuery::query()->create([
            'search_query' => 'old query',
            'title' => 'Existing title',
            'count' => 2,
            'has_result' => false,
        ]);

        livewire(EditSearchQuery::class, ['record' => $searchQuery->getKey()])
            ->fillForm([
                'title' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas('search_queries', [
            'id' => $searchQuery->id,
            'title' => null,
        ]);
    });

    it('can delete a search query from edit page delete action', function () {
        $searchQuery = SearchQuery::query()->create([
            'search_query' => 'delete me',
            'title' => null,
            'count' => 1,
            'has_result' => false,
        ]);

        livewire(EditSearchQuery::class, ['record' => $searchQuery->getKey()])
            ->assertActionExists(DeleteAction::class)
            ->callAction(DeleteAction::class);

        assertDatabaseMissing('search_queries', ['id' => $searchQuery->id]);
    });

    it('renders terms relation manager on edit page', function () {
        $searchQuery = SearchQuery::factory()->create();

        livewire(EditSearchQuery::class, ['record' => $searchQuery->getKey()])
            ->assertSuccessful()
            ->assertSeeLivewire(TermsRelationManager::class);
    });

    it('lists related terms in relation manager', function () {
        $searchQuery = SearchQuery::factory()->create();
        $relatedTerms = Term::factory()->count(2)->create();

        $searchQuery->terms()->attach($relatedTerms->pluck('id'));

        livewire(TermsRelationManager::class, [
            'ownerRecord' => $searchQuery,
            'pageClass' => EditSearchQuery::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($relatedTerms);
    });

    it('can detach a term from search query', function () {
        $searchQuery = SearchQuery::factory()->create();
        $term = Term::factory()->create();
        $searchQuery->terms()->attach($term);

        livewire(TermsRelationManager::class, [
            'ownerRecord' => $searchQuery,
            'pageClass' => EditSearchQuery::class,
        ])
            ->callTableAction(DetachAction::class, $term)
            ->assertSuccessful();

        expect($searchQuery->terms()->count())->toBe(0);
    });

    it('can bulk detach terms from search query', function () {
        $searchQuery = SearchQuery::factory()->create();
        $terms = Term::factory()->count(3)->create();

        $searchQuery->terms()->attach($terms->pluck('id'));

        expect($searchQuery->terms()->count())->toBe(3);

        livewire(TermsRelationManager::class, [
            'ownerRecord' => $searchQuery,
            'pageClass' => EditSearchQuery::class,
        ])
            ->assertCanSeeTableRecords($terms)
            ->callTableBulkAction(DetachBulkAction::class, $terms)
            ->assertSuccessful();

        expect($searchQuery->refresh()->terms()->count())->toBe(0);
    });
});
