<?php

use App\Filament\Resources\TermProposals\Pages\ListTermProposals;
use App\Filament\Resources\TermProposals\TermProposalResource;
use App\Models\Term;
use App\Models\TermProposal;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

describe('List Term Proposals Page', function () {
    it('renders term proposals table with required columns and keeps email hidden by default', function () {
        $proposal = TermProposal::factory()->for(Term::factory())->create();

        livewire(ListTermProposals::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$proposal])
            ->assertCanRenderTableColumn('term.title')
            ->assertCanRenderTableColumn('description')
            ->assertCanRenderTableColumn('created_at')
            ->assertCanNotRenderTableColumn('email')
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn('email');
    });

    it('can apply a proposal and edit the final term description before saving', function () {
        $term = Term::factory()->create([
            'description' => 'Old term description',
        ]);

        $proposal = TermProposal::factory()->for($term)->create([
            'description' => 'Suggested new description',
        ]);

        $expectedDescription = 'Updated from proposal with moderator edits';

        livewire(ListTermProposals::class)
            ->callAction(TestAction::make('apply')->table()->table($proposal), data: [
                'description' => $expectedDescription,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $term->refresh();

        expect(strip_tags($term->description))->toBe($expectedDescription);

        assertDatabaseMissing('term_proposals', [
            'id' => $proposal->id,
        ]);
    });
});

describe('Create and Edit Term Proposal Restrictions', function () {
    it('does not render the create action button on the list page', function () {
        livewire(ListTermProposals::class)
            ->assertSuccessful()
            ->assertActionDoesNotExist(CreateAction::class);
    });

    it('does not render the edit action on the list page', function () {
        livewire(ListTermProposals::class)
            ->assertSuccessful()
            ->assertActionDoesNotExist(TestAction::make(EditAction::class)->table());
    });

    it('does not have create and edit pages registered in the resource', function () {
        $pages = TermProposalResource::getPages();

        expect($pages)
            ->not->toHaveKey('create')
            ->not->toHaveKey('edit');
    });

    it('returns 404 when trying to access the create page directly', function () {
        $createUrl = TermProposalResource::getUrl('index').'/create';

        $this->get($createUrl)
            ->assertNotFound();
    });

    it('returns 404 when trying to access the edit page directly', function () {
        $proposal = TermProposal::factory()->for(Term::factory())->create();

        $editUrl = TermProposalResource::getUrl('index').'/'.$proposal->getKey().'/edit';

        $this->get($editUrl)
            ->assertNotFound();
    });
});
