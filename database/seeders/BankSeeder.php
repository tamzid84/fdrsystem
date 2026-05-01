<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $govtBanks = [
            'Sonali Bank PLC',
            'Janata Bank PLC',
            'Agrani Bank PLC',
            'Rupali Bank PLC',
            'BASIC Bank PLC',
            'Bangladesh Development Bank PLC',
            'Bangladesh Krishi Bank',
            'Rajshahi Krishi Unnon Bank',
            'Probashi Kollan Bank',
        ];

        $banks = [
            // Private Commercial Banks
            'Pubali Bank PLC',
            'Uttara Bank PLC',
            'AB Bank PLC',
            'IFIC Bank PLC',
            'United Commercial Bank PLC',
            'City Bank',
            'National Bank PLC',
            'National Credit & Commerce Bank PLC',
            'Eastern Bank PLC',
            'Dutch Bangla Bank PLC',
            'Dhaka Bank PLC',
            'Prime Bank PLC',
            'Mutual Trust Bank PLC',
            'Southeast Bank PLC',
            'Bangladesh Commerce Bank PLC',
            'One Bank PLC',
            'Trust Bank PLC',
            'Premier Bank PLC',
            'Bank Asia PLC',
            'Marcentile Bank PLC',
            'BRAC Bank PLC',
            'Jamuna Bank PLC',
            'NRBC Bank PLC',
            'NRB Bank PLC',
            'Padma Bank PLC',
            'Modhumoti Bank PLC',
            'Midland Bank PLC',
            'Meghna Bank PLC',
            'SBAC Bank PLC',
            'Simanto Bank PLC',
            'Community Bank PLC',
            'Bangal Commercial Bank PLC',
            'Citizen Bank PLC',

            // Islamic Banks (PRIVATE)
            'Islami Bank Bangladesh PLC',
            'ICB Islami Bank PLC',
            'Al-Arafa Islami Bank PLC',
            'Social Islami Bank PLC',
            'EXIM Bank PLC',
            'First Security Islami Bank PLC',
            'Standard Bank PLC',
            'Shahjalal Islami Bank PLC',
            'Union Bank PLC',
            'Global Islami Bank PLC',
        ];

        // ✅ Your provided Sylhet areas (cleaned + unique)
        $areas = [
            'Telikhal',
            'Islampur West',
            'Islampur East',
            'Isakalas',
            'Uttar Ronikhai',
            'Dakshin Ronikhai',
            'Fothepur',
            'Rustampur',
            'Paschim Jaflong',
            'Purba Jaflong',
            'Lengura',
            'Alirgaon',
            'Nandirgaon',
            'Towakul',
            'Daubari',
            'Nijpat',
            'Jaintapur',
            'Charikatha',
            'Darbast',
            'Fatehpur',
            'Chiknagul',
            'Rajagonj',
            'Lakshiprashad Purbo',
            'Lakshiprashad Pashim',
            'Digirpar Purbo',
            'Satbakh',
            'Barachotul',
            'Kanaighat',
            'Dakhin Banigram',
            'Jinghabari',
            'Manikpur',
            'Sultanpur',
            'Barohal',
            'Birorsri',
            'Kajalshah',
            'Kolachora',
            'Zakiganj',
            'Barothakuri',
            'Kaskanakpur',
        ];

        $data = [];
        $i = 1;

        foreach ($banks as $bank) {
            foreach ($areas as $area) {

                if (count($data) >= 120) break 2;

                $type = in_array($bank, $govtBanks) ? 'govt' : 'private';

                $data[] = [
                    'name' => $bank,
                    'type' => $type,
                    'branch_name' => $area . ' Branch',
                    'account_number' => null,
                    'routing_number' => 'SYL' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'phone' => '01' . rand(700000000, 999999999),
                    'address' => $area . ', Sylhet',
                    'total_investment' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $i++;
            }
        }

        DB::table('banks')->insert($data);
    }
}