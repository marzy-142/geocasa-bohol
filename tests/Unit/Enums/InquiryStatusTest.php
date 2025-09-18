<?php

namespace Tests\Unit\Enums;

use App\Enums\InquiryStatus;
use Tests\TestCase;

class InquiryStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_status_values()
    {
        $this->assertEquals('new', InquiryStatus::NEW->value);
        $this->assertEquals('contacted', InquiryStatus::CONTACTED->value);
        $this->assertEquals('scheduled', InquiryStatus::SCHEDULED->value);
        $this->assertEquals('completed', InquiryStatus::COMPLETED->value);
        $this->assertEquals('closed', InquiryStatus::CLOSED->value);
    }

    /** @test */
    public function it_provides_correct_labels()
    {
        $this->assertEquals('New Inquiry', InquiryStatus::NEW->getLabel());
        $this->assertEquals('Contacted', InquiryStatus::CONTACTED->getLabel());
        $this->assertEquals('Scheduled', InquiryStatus::SCHEDULED->getLabel());
        $this->assertEquals('Completed', InquiryStatus::COMPLETED->getLabel());
        $this->assertEquals('Closed', InquiryStatus::CLOSED->getLabel());
    }

    /** @test */
    public function it_provides_correct_css_classes()
    {
        $this->assertEquals('bg-blue-100 text-blue-800', InquiryStatus::NEW->getCssClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', InquiryStatus::CONTACTED->getCssClass());
        $this->assertEquals('bg-purple-100 text-purple-800', InquiryStatus::SCHEDULED->getCssClass());
        $this->assertEquals('bg-green-100 text-green-800', InquiryStatus::COMPLETED->getCssClass());
        $this->assertEquals('bg-gray-100 text-gray-800', InquiryStatus::CLOSED->getCssClass());
    }

    /** @test */
    public function it_validates_transitions_from_new_status()
    {
        $this->assertTrue(InquiryStatus::NEW->canTransitionTo(InquiryStatus::CONTACTED));
        $this->assertTrue(InquiryStatus::NEW->canTransitionTo(InquiryStatus::CLOSED));
        $this->assertFalse(InquiryStatus::NEW->canTransitionTo(InquiryStatus::SCHEDULED));
        $this->assertFalse(InquiryStatus::NEW->canTransitionTo(InquiryStatus::COMPLETED));
        $this->assertFalse(InquiryStatus::NEW->canTransitionTo(InquiryStatus::NEW));
    }

    /** @test */
    public function it_validates_transitions_from_contacted_status()
    {
        $this->assertTrue(InquiryStatus::CONTACTED->canTransitionTo(InquiryStatus::SCHEDULED));
        $this->assertTrue(InquiryStatus::CONTACTED->canTransitionTo(InquiryStatus::CLOSED));
        $this->assertFalse(InquiryStatus::CONTACTED->canTransitionTo(InquiryStatus::NEW));
        $this->assertFalse(InquiryStatus::CONTACTED->canTransitionTo(InquiryStatus::COMPLETED));
        $this->assertFalse(InquiryStatus::CONTACTED->canTransitionTo(InquiryStatus::CONTACTED));
    }

    /** @test */
    public function it_validates_transitions_from_scheduled_status()
    {
        $this->assertTrue(InquiryStatus::SCHEDULED->canTransitionTo(InquiryStatus::COMPLETED));
        $this->assertTrue(InquiryStatus::SCHEDULED->canTransitionTo(InquiryStatus::CLOSED));
        $this->assertFalse(InquiryStatus::SCHEDULED->canTransitionTo(InquiryStatus::NEW));
        $this->assertFalse(InquiryStatus::SCHEDULED->canTransitionTo(InquiryStatus::CONTACTED));
        $this->assertFalse(InquiryStatus::SCHEDULED->canTransitionTo(InquiryStatus::SCHEDULED));
    }

    /** @test */
    public function it_validates_transitions_from_completed_status()
    {
        $this->assertTrue(InquiryStatus::COMPLETED->canTransitionTo(InquiryStatus::CLOSED));
        $this->assertFalse(InquiryStatus::COMPLETED->canTransitionTo(InquiryStatus::NEW));
        $this->assertFalse(InquiryStatus::COMPLETED->canTransitionTo(InquiryStatus::CONTACTED));
        $this->assertFalse(InquiryStatus::COMPLETED->canTransitionTo(InquiryStatus::SCHEDULED));
        $this->assertFalse(InquiryStatus::COMPLETED->canTransitionTo(InquiryStatus::COMPLETED));
    }

    /** @test */
    public function it_validates_transitions_from_closed_status()
    {
        $this->assertFalse(InquiryStatus::CLOSED->canTransitionTo(InquiryStatus::NEW));
        $this->assertFalse(InquiryStatus::CLOSED->canTransitionTo(InquiryStatus::CONTACTED));
        $this->assertFalse(InquiryStatus::CLOSED->canTransitionTo(InquiryStatus::SCHEDULED));
        $this->assertFalse(InquiryStatus::CLOSED->canTransitionTo(InquiryStatus::COMPLETED));
        $this->assertFalse(InquiryStatus::CLOSED->canTransitionTo(InquiryStatus::CLOSED));
    }

    /** @test */
    public function it_identifies_terminal_statuses()
    {
        $this->assertFalse(InquiryStatus::NEW->isTerminal());
        $this->assertFalse(InquiryStatus::CONTACTED->isTerminal());
        $this->assertFalse(InquiryStatus::SCHEDULED->isTerminal());
        $this->assertTrue(InquiryStatus::COMPLETED->isTerminal());
        $this->assertTrue(InquiryStatus::CLOSED->isTerminal());
    }

    /** @test */
    public function it_identifies_statuses_requiring_broker_response()
    {
        $this->assertTrue(InquiryStatus::NEW->requiresBrokerResponse());
        $this->assertFalse(InquiryStatus::CONTACTED->requiresBrokerResponse());
        $this->assertFalse(InquiryStatus::SCHEDULED->requiresBrokerResponse());
        $this->assertFalse(InquiryStatus::COMPLETED->requiresBrokerResponse());
        $this->assertFalse(InquiryStatus::CLOSED->requiresBrokerResponse());
    }

    /** @test */
    public function it_returns_all_statuses()
    {
        $allStatuses = InquiryStatus::all();
        
        $this->assertCount(5, $allStatuses);
        $this->assertContains(InquiryStatus::NEW, $allStatuses);
        $this->assertContains(InquiryStatus::CONTACTED, $allStatuses);
        $this->assertContains(InquiryStatus::SCHEDULED, $allStatuses);
        $this->assertContains(InquiryStatus::COMPLETED, $allStatuses);
        $this->assertContains(InquiryStatus::CLOSED, $allStatuses);
    }

    /** @test */
    public function it_returns_active_statuses()
    {
        $activeStatuses = InquiryStatus::active();
        
        $this->assertCount(3, $activeStatuses);
        $this->assertContains(InquiryStatus::NEW, $activeStatuses);
        $this->assertContains(InquiryStatus::CONTACTED, $activeStatuses);
        $this->assertContains(InquiryStatus::SCHEDULED, $activeStatuses);
        $this->assertNotContains(InquiryStatus::COMPLETED, $activeStatuses);
        $this->assertNotContains(InquiryStatus::CLOSED, $activeStatuses);
    }

    /** @test */
    public function it_returns_terminal_statuses()
    {
        $terminalStatuses = InquiryStatus::terminal();
        
        $this->assertCount(2, $terminalStatuses);
        $this->assertContains(InquiryStatus::COMPLETED, $terminalStatuses);
        $this->assertContains(InquiryStatus::CLOSED, $terminalStatuses);
        $this->assertNotContains(InquiryStatus::NEW, $terminalStatuses);
        $this->assertNotContains(InquiryStatus::CONTACTED, $terminalStatuses);
        $this->assertNotContains(InquiryStatus::SCHEDULED, $terminalStatuses);
    }

    /** @test */
    public function it_returns_statuses_requiring_broker_response()
    {
        $brokerResponseStatuses = InquiryStatus::getStatusesRequiringBrokerResponse();
        
        $this->assertCount(1, $brokerResponseStatuses);
        $this->assertContains(InquiryStatus::NEW, $brokerResponseStatuses);
        $this->assertNotContains(InquiryStatus::CONTACTED, $brokerResponseStatuses);
        $this->assertNotContains(InquiryStatus::SCHEDULED, $brokerResponseStatuses);
        $this->assertNotContains(InquiryStatus::COMPLETED, $brokerResponseStatuses);
        $this->assertNotContains(InquiryStatus::CLOSED, $brokerResponseStatuses);
    }

    /** @test */
    public function it_creates_status_from_string()
    {
        $this->assertEquals(InquiryStatus::NEW, InquiryStatus::from('new'));
        $this->assertEquals(InquiryStatus::CONTACTED, InquiryStatus::from('contacted'));
        $this->assertEquals(InquiryStatus::SCHEDULED, InquiryStatus::from('scheduled'));
        $this->assertEquals(InquiryStatus::COMPLETED, InquiryStatus::from('completed'));
        $this->assertEquals(InquiryStatus::CLOSED, InquiryStatus::from('closed'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_status()
    {
        $this->expectException(\ValueError::class);
        InquiryStatus::from('invalid_status');
    }

    /** @test */
    public function it_tries_to_create_status_from_string()
    {
        $this->assertEquals(InquiryStatus::NEW, InquiryStatus::tryFrom('new'));
        $this->assertEquals(InquiryStatus::CONTACTED, InquiryStatus::tryFrom('contacted'));
        $this->assertNull(InquiryStatus::tryFrom('invalid_status'));
    }

    /** @test */
    public function it_provides_status_for_select_options()
    {
        $options = InquiryStatus::forSelect();
        
        $this->assertIsArray($options);
        $this->assertEquals('New Inquiry', $options['new']);
        $this->assertEquals('Contacted', $options['contacted']);
        $this->assertEquals('Scheduled', $options['scheduled']);
        $this->assertEquals('Completed', $options['completed']);
        $this->assertEquals('Closed', $options['closed']);
    }

    /** @test */
    public function it_provides_active_status_for_select_options()
    {
        $options = InquiryStatus::forSelectActive();
        
        $this->assertIsArray($options);
        $this->assertCount(3, $options);
        $this->assertEquals('New Inquiry', $options['new']);
        $this->assertEquals('Contacted', $options['contacted']);
        $this->assertEquals('Scheduled', $options['scheduled']);
        $this->assertArrayNotHasKey('completed', $options);
        $this->assertArrayNotHasKey('closed', $options);
    }
}