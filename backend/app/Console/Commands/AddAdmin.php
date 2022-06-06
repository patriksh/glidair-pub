<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;

class AddAdmin extends Command
{
    protected $signature = 'add:admin';

    protected $description = 'Dodaj administratora.';

    public function __construct()
    {
        parent::__construct();
    }

    // Konzolna komanda za dodavanje administratora.
    public function handle()
    {
        $input = [
            'name' => $this->ask('Unesi ime'),
            'username' => $this->ask('Unesi korisničko ime'),
            'email' => $this->ask('Unesi email adresu'),
            'password' => bcrypt($this->secret('Unesi zaporku'))
        ];

        Admin::create($input);
        $this->info('Administrator je uspješno dodan.');

        return Command::SUCCESS;
    }
}
