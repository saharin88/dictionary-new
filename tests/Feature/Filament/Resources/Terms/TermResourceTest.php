<?php

use App\Filament\Resources\Terms\Pages\CreateTerm;
use App\Filament\Resources\Terms\Pages\EditTerm;
use App\Filament\Resources\Terms\Pages\ListTerms;
use App\Filament\Resources\Terms\RelationManagers\SearchQueriesRelationManager;
use App\Filament\Resources\Terms\RelationManagers\TermProposalsRelationManager;
use App\Filament\Resources\Terms\TermResource;
use App\Models\SearchQuery;
use App\Models\Term;
use App\Models\TermProposal;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Columns\ToggleColumn;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

describe('List Terms Page', function () {
    it('renders terms list table with required columns', function () {
        livewire(ListTerms::class)
            ->assertSuccessful()
            ->assertCanRenderTableColumn('title')
            ->assertCanRenderTableColumn('description')
            ->assertCanRenderTableColumn('searchQueries.search_query')
            ->assertCanRenderTableColumn('is_published')
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn('slug')
            ->assertCanRenderTableColumn('created_at')
            ->assertCanRenderTableColumn('updated_at');
    });

    it('shows term title, description and search queries in the table', function () {
        $term = Term::factory()->create([
            'title' => 'Alpha Term',
            'description' => 'Alpha term description',
            'is_published' => true,
        ]);

        $term->searchQueries()->createMany([
            ['search_query' => 'alpha'],
            ['search_query' => 'dictionary'],
        ]);

        livewire(ListTerms::class)
            ->assertCanSeeTableRecords([$term])
            ->assertTableColumnStateSet('title', 'Alpha Term', record: $term)
            ->assertTableColumnStateSet('description', 'Alpha term description', record: $term)
            ->assertSee('alpha')
            ->assertSee('dictionary');
    });
    it('renders hidden-by-default toggleable columns after toggling all columns on', function () {
        $term = Term::factory()->create();

        livewire(ListTerms::class)
            ->assertCanSeeTableRecords([$term])
            ->assertCanNotRenderTableColumn('slug')
            ->assertCanNotRenderTableColumn('created_at')
            ->assertCanNotRenderTableColumn('updated_at')
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn('slug')
            ->assertCanRenderTableColumn('created_at')
            ->assertCanRenderTableColumn('updated_at');
    });

    it('can toggle all toggleable columns off and on', function () {
        $term = Term::factory()->create();
        $term->searchQueries()->createMany([
            ['search_query' => 'alpha'],
            ['search_query' => 'dictionary'],
        ]);

        livewire(ListTerms::class)
            ->assertCanSeeTableRecords([$term])
            ->assertCanRenderTableColumn('searchQueries.search_query')
            ->assertCanRenderTableColumn('is_published')
            ->toggleAllTableColumns(false)
            ->assertCanNotRenderTableColumn('searchQueries.search_query')
            ->assertCanNotRenderTableColumn('is_published')
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn('searchQueries.search_query')
            ->assertCanRenderTableColumn('is_published');
    });

    it('shows is_published as a toggle column with correct state', function () {
        $publishedTerm = Term::factory()->published()->create();
        $unpublishedTerm = Term::factory()->unpublished()->create();

        livewire(ListTerms::class)
            ->assertTableColumnExists('is_published', fn ($column): bool => $column instanceof ToggleColumn)
            ->assertTableColumnStateSet('is_published', true, record: $publishedTerm)
            ->assertTableColumnStateSet('is_published', false, record: $unpublishedTerm);
    });

    it('updates is_published via table toggle column action', function () {
        $term = Term::factory()->unpublished()->create();

        livewire(ListTerms::class)
            ->call('updateTableColumnState', 'is_published', (string) $term->getKey(), true)
            ->assertTableColumnStateSet('is_published', true, record: $term);

        expect($term->refresh()->is_published)->toBeTrue();

        livewire(ListTerms::class)
            ->call('updateTableColumnState', 'is_published', (string) $term->getKey(), false)
            ->assertTableColumnStateSet('is_published', false, record: $term);

        expect($term->refresh()->is_published)->toBeFalse();
    });
    it('filters terms by is_published select filter', function () {
        $publishedTerm = Term::factory()->published()->create();
        $unpublishedTerm = Term::factory()->unpublished()->create();

        livewire(ListTerms::class)
            ->assertTableFilterVisible('is_published')
            ->filterTable('is_published', true)
            ->assertCanSeeTableRecords([$publishedTerm])
            ->assertCanNotSeeTableRecords([$unpublishedTerm])
            ->filterTable('is_published', false)
            ->assertCanSeeTableRecords([$unpublishedTerm])
            ->assertCanNotSeeTableRecords([$publishedTerm]);
    });

    it('filters terms by search queries select filter', function () {
        $alphaTerm = Term::factory()->create();
        $alphaQuery = $alphaTerm->searchQueries()->create(['search_query' => 'alpha']);

        $betaTerm = Term::factory()->create();
        $betaQuery = $betaTerm->searchQueries()->create(['search_query' => 'beta']);

        $gammaTerm = Term::factory()->create();
        $gammaTerm->searchQueries()->create(['search_query' => 'gamma']);

        livewire(ListTerms::class)
            ->assertTableFilterVisible('searchQueries')
            ->filterTable('searchQueries', [$alphaQuery->id, $betaQuery->id])
            ->assertCanSeeTableRecords([$alphaTerm, $betaTerm])
            ->assertCanNotSeeTableRecords([$gammaTerm]);
    });

    it('filters duplicate terms by title', function () {
        $firstDuplicate = Term::factory()->create([
            'title' => 'Duplicate title',
            'slug' => 'duplicate-title-1',
            'description' => 'First description',
        ]);
        $secondDuplicate = Term::factory()->create([
            'title' => 'Duplicate title',
            'slug' => 'duplicate-title-2',
            'description' => 'Second description',
        ]);
        $uniqueTerm = Term::factory()->create([
            'title' => 'Unique title',
            'slug' => 'unique-title',
            'description' => 'Unique description',
        ]);

        livewire(ListTerms::class)
            ->assertTableFilterVisible('duplicates')
            ->filterTable('duplicates', 'title')
            ->assertCanSeeTableRecords([$firstDuplicate, $secondDuplicate])
            ->assertCanNotSeeTableRecords([$uniqueTerm]);
    });

    it('filters duplicate terms by title and description', function () {
        $firstDuplicate = Term::factory()->create([
            'title' => 'Fully duplicate title',
            'slug' => 'fully-duplicate-title-1',
            'description' => 'Fully duplicate description',
        ]);
        $secondDuplicate = Term::factory()->create([
            'title' => 'Fully duplicate title',
            'slug' => 'fully-duplicate-title-2',
            'description' => 'Fully duplicate description',
        ]);
        $sameTitleDifferentDescription = Term::factory()->create([
            'title' => 'Fully duplicate title',
            'slug' => 'fully-duplicate-title-3',
            'description' => 'Different description',
        ]);

        livewire(ListTerms::class)
            ->filterTable('duplicates', 'title_description')
            ->assertCanSeeTableRecords([$firstDuplicate, $secondDuplicate])
            ->assertCanNotSeeTableRecords([$sameTitleDifferentDescription]);
    });

    it('sorts duplicate terms by title ascending when duplicates filter is active', function () {
        $betaFirst = Term::factory()->create([
            'title' => 'Beta duplicate title',
            'slug' => 'beta-duplicate-title-1',
        ]);
        $betaSecond = Term::factory()->create([
            'title' => 'Beta duplicate title',
            'slug' => 'beta-duplicate-title-2',
        ]);
        $alphaFirst = Term::factory()->create([
            'title' => 'Alpha duplicate title',
            'slug' => 'alpha-duplicate-title-1',
        ]);
        $alphaSecond = Term::factory()->create([
            'title' => 'Alpha duplicate title',
            'slug' => 'alpha-duplicate-title-2',
        ]);

        livewire(ListTerms::class)
            ->filterTable('duplicates', 'title')
            ->assertCanSeeTableRecords([$betaFirst, $betaSecond, $alphaFirst, $alphaSecond])
            ->assertSeeInOrder(['Alpha duplicate title', 'Beta duplicate title']);
    });

    it('shows import and export actions in the page header', function () {
        livewire(ListTerms::class)
            ->assertActionExists('import')
            ->assertActionExists('export');
    });

    it('shows bulk export action in the table bulk actions', function () {
        livewire(ListTerms::class)
            ->assertActionExists(TestAction::make(ExportBulkAction::class)->table()->bulk());
    });

    it('can delete terms using bulk delete action', function () {
        $terms = Term::factory()->count(2)->create();

        livewire(ListTerms::class)
            ->selectTableRecords($terms->pluck('id'))
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        foreach ($terms as $term) {
            assertDatabaseMissing('terms', ['id' => $term->id]);
        }
    });

    it('can toggle publish status of terms using bulk actions', function (string $initialState, string $action, bool $expectedStatus) {
        $terms = Term::factory()->count(2)->{$initialState}()->create();

        livewire(ListTerms::class)
            ->selectTableRecords($terms->pluck('id'))
            ->callAction(TestAction::make($action)->table()->bulk())
            ->assertNotified();

        expect(Term::whereIn('id', $terms->pluck('id'))->where('is_published', $expectedStatus)->count())
            ->toBe(2);
    })->with([
        'publish action' => ['unpublished', 'publish', true],
        'unpublish action' => ['published', 'unpublish', false],
    ]);
});

describe('Create Term Page', function () {
    it('can create a term from create page', function () {
        livewire(CreateTerm::class)
            ->assertOk()
            ->fillForm([
                'title' => 'Created Term',
                'slug' => 'created-term',
                'description' => 'Created term description',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas('terms', [
            'title' => 'Created Term',
            'slug' => 'created-term',
            'description' => '<p>Created term description</p>',
            'is_published' => true,
        ]);
    });
});

describe('Edit Term Page', function () {
    it('can edit a term from edit page', function () {
        $term = Term::factory()->create([
            'title' => 'Initial Term',
            'slug' => 'initial-term',
            'description' => 'Initial description',
            'is_published' => false,
        ]);

        livewire(EditTerm::class, ['record' => $term->getRouteKey()])
            ->assertOk()
            ->fillForm([
                'title' => 'Updated Term',
                'slug' => 'updated-term',
                'description' => 'Updated description',
                'is_published' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas('terms', [
            'id' => $term->id,
            'title' => 'Updated Term',
            'slug' => 'updated-term',
            'description' => '<p>Updated description</p>',
            'is_published' => true,
        ]);
    });

    it('can delete a term from edit page delete action', function () {
        $term = Term::factory()->create();

        livewire(EditTerm::class, ['record' => $term->getRouteKey()])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        assertDatabaseMissing('terms', ['id' => $term->id]);
    });

    it('renders search queries relation manager on edit page', function () {
        $term = Term::factory()->create();

        livewire(EditTerm::class, ['record' => $term->getRouteKey()])
            ->assertSuccessful()
            ->assertSeeLivewire(SearchQueriesRelationManager::class);
    });

    it('registers term proposals relation manager for the term resource', function () {
        expect(TermResource::getRelations())
            ->toContain(TermProposalsRelationManager::class);
    });

    it('lists related term proposals in relation manager', function () {
        $term = Term::factory()->create();
        $proposals = TermProposal::factory()->count(2)->for($term)->create();

        livewire(TermProposalsRelationManager::class, [
            'ownerRecord' => $term,
            'pageClass' => EditTerm::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($proposals);
    });

    it('can apply a term proposal from relation manager', function () {
        $term = Term::factory()->create([
            'description' => 'Initial description',
        ]);
        $proposal = TermProposal::factory()->for($term)->create([
            'description' => 'Suggested description',
        ]);

        livewire(TermProposalsRelationManager::class, [
            'ownerRecord' => $term,
            'pageClass' => EditTerm::class,
        ])
            ->callAction(TestAction::make('apply')->table()->table($proposal), data: [
                'description' => 'Updated from proposal with moderator edits',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas('terms', [
            'id' => $term->id,
            'description' => '<p>Updated from proposal with moderator edits</p>',
        ]);

        assertDatabaseMissing('term_proposals', [
            'id' => $proposal->id,
        ]);
    });

    it('registers search queries relation manager for the term resource', function () {
        expect(TermResource::getRelations())
            ->toContain(SearchQueriesRelationManager::class);
    });

    it('lists related search queries in relation manager', function () {
        $term = Term::factory()->create();
        $relatedSearchQueries = SearchQuery::factory()->count(2)->create();

        $term->searchQueries()->attach($relatedSearchQueries->pluck('id'));

        livewire(SearchQueriesRelationManager::class, [
            'ownerRecord' => $term,
            'pageClass' => EditTerm::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($relatedSearchQueries);
    });

    it('can detach a search query from term', function () {
        $term = Term::factory()->create();
        $searchQuery = SearchQuery::factory()->create();
        $term->searchQueries()->attach($searchQuery);

        livewire(SearchQueriesRelationManager::class, [
            'ownerRecord' => $term,
            'pageClass' => EditTerm::class,
        ])
            ->callTableAction(DetachAction::class, $searchQuery)
            ->assertSuccessful();

        expect($term->searchQueries()->count())->toBe(0);
    });

    it('can bulk detach search queries from term', function () {
        $term = Term::factory()->create();
        $searchQueries = SearchQuery::factory()->count(3)->create();
        $term->searchQueries()->attach($searchQueries->pluck('id'));

        livewire(SearchQueriesRelationManager::class, [
            'ownerRecord' => $term,
            'pageClass' => EditTerm::class,
        ])
            ->callTableBulkAction(DetachBulkAction::class, $searchQueries)
            ->assertSuccessful();

        expect($term->refresh()->searchQueries()->count())->toBe(0);
    });
});
