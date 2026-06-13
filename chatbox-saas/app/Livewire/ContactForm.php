<?php

namespace App\Livewire;

use App\Models\Deal;
use App\Models\LandingPageAnalytic;
use App\Models\LandingPageSetting;
use App\Models\Lead;
use App\Models\Pipeline;
use Livewire\Component;

class ContactForm extends Component
{
    public $name = '';

    public $company = '';

    public $role = '';

    public $email = '';

    public $whatsapp = '';

    public $attendants_qty = '';

    public $segment = '';

    public $message = '';

    public $success = false;

    // Tracking fields
    public $utm_source = '';

    public $utm_medium = '';

    public $utm_campaign = '';

    public $utm_content = '';

    public $utm_term = '';

    public $referer = '';

    public $browser = '';

    public $device = '';

    public $os = '';

    public $country = '';

    public $city = '';

    public $ip = '';

    public $settings;

    protected $rules = [
        'name' => 'required|min:3',
        'company' => 'required|min:2',
        'role' => 'nullable|string',
        'email' => 'required|email',
        'whatsapp' => 'required|string|min:10',
        'attendants_qty' => 'nullable|integer',
        'segment' => 'nullable|string',
        'message' => 'nullable|min:5',
    ];

    public function mount()
    {
        $this->settings = LandingPageSetting::first();
    }

    public function submit()
    {
        $this->validate();

        $lead = Lead::create([
            'name' => $this->name,
            'company' => $this->company,
            'role' => $this->role,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'attendants_qty' => $this->attendants_qty ?: null,
            'segment' => $this->segment,
            'message' => $this->message,
            'status' => 'Novo',
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_content' => $this->utm_content,
            'utm_term' => $this->utm_term,
            'referer' => $this->referer,
            'browser' => $this->browser,
            'device' => $this->device,
            'os' => $this->os,
            'country' => $this->country,
            'city' => $this->city,
            'ip' => $this->ip,
            'origin' => 'website',
        ]);

        // Create Analytics Conversion Event
        LandingPageAnalytic::create([
            'type' => 'conversion',
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_content' => $this->utm_content,
            'utm_term' => $this->utm_term,
            'referer' => $this->referer,
            'browser' => $this->browser,
            'device' => $this->device,
            'os' => $this->os,
            'ip' => $this->ip,
        ]);

        // Attempt to create a Pipeline Deal if Pipeline exists for the "first company" (since it's a SaaS root landing page)
        // A generic approach: create it in the main company's pipeline.
        // Assuming the main company is the first one or we can just leave it as Lead and allow an admin to process it.
        $pipeline = Pipeline::first();
        if ($pipeline && $pipeline->stages()->exists()) {
            Deal::create([
                'company_id' => $pipeline->company_id,
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $pipeline->stages()->first()->id,
                'title' => 'Contato Site: '.$this->name.' - '.$this->company,
                'value' => 0,
                'status' => 'open',
                'custom_fields' => [
                    'lead_id' => $lead->id,
                    'whatsapp' => $this->whatsapp,
                    'email' => $this->email,
                ],
            ]);
        }

        $this->success = true;
        $this->reset([
            'name', 'company', 'role', 'email', 'whatsapp',
            'attendants_qty', 'segment', 'message',
        ]);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
