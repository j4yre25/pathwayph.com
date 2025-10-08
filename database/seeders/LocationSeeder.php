<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Inserts a set of barangay / street / address rows for General Santos City.
     * Adjust field names if your locations table uses different column names.
     */
    public function run()
    {
        $now = now();

        $data = [
            'Lagao' => [
                '1st Avenue', '1st Street', '3rd Avenue', '3rd Street',
                'A. Casquejo Sr. Street', 'Agustin Ferrariz Street', 'Amado Fenequito Street',
                'Apitong Street', 'Ardonia Street', 'Aruba Street', 'Asai Road', 'Asai Street',
                'Balite Street', 'Bougainvilla Street', 'Barbuda Street', 'Block 1', 'Block 2', 'Block 3',
                'Bonaire Street', 'Cabel Street', 'Cagampang Street', 'Camachile Street', 'Camia Street',
                'Camias Street', 'Capareda Street', 'Catalino Cabe Street', 'Cecilio Tugonon Street',
                'David G. Calina Street', 'De Dios Street', 'De Vera Street', 'Divinagracia Street',
                'Donato Quinto, Sr. Street', 'Emerald Street', 'Eulogio Rodriguez, Sr. Street',
                'Eusebio Fernandez Street', 'Fabian M. Natad, Sr. Drive', 'G. Misa Street', 'Geronimo Street',
                'Godofredo Albania Street', 'Goldentop Street', 'Gonave Street', 'Grenada Street',
                'Gumamela Street', 'Honduras Street', 'Honorio Arriola Street', 'Irineo Santiago Boulevard',
                'Jorge Royeca Boulevard', 'Jose Catolico, Sr. Avenue', 'Juan Crisologo Street',
                'Ladislao Gavilo, Sr. Street', 'Ladrera Street', 'Leodegario Arradaza Street',
                'Leonor Gonzales Street', 'Lucio Congson Street', 'Manga Street', 'Margarita Street',
                'Mateo Street', 'Molave Street', 'Montserrat Street', 'Narra Street', 'Natividad Street',
                'NDDU Service Road', 'Nevis Street', 'NLSA Road', 'Odi Street',
                'Pacifico Bugarin Street', 'Parrot Street', 'Rafael Alunan Street', 'Rivera Street',
                'Rosas Street', 'Roxas Avenue', 'Saint Barths Street', 'Salinda Street', 'San Pablo Street',
                'San Pedro Street', 'Sarangani-Davao del Sur Coastal Road', 'Sineguelas Street',
                'Sitio Toning Road', 'Solarte Street', 'Talisay Street', 'Tancinco Street', 'Tindalo Street',
                'Tiongson Street', 'Tiongson Street Extension', 'Ventilacion Street', 'Ventura Damicog Street',
                'Veranza Drive', 'Villareal Street', 'West Side Street', 'Yumang Street'
            ],
            'Calumpang' => [
                'Agriculture Road','Albert Morrow Boulevard','BA & A Road','Bayanihan Road','BQ Road',
                'Calumpang PS Road','Centennial Lane','Engineering Road','Filipino-American Friendship Avenue',
                'GYM Road','Infirmary Road','Magsaysay Avenue','Maguindanao Avenue','Makar-Kiamba Road','Marang Street',
                'Natural Sciences and Mathematics Road','OSA Road','PPD Road','Saeg Road','Saint Mary Street',
                'Saint Michael Street','Social Sciences and Humanities Road','Strella Road','Zone 3 Road','Zone 4 Road',
            ],
           'Apopong' => [
                'Albert Morrow Boulevard','Apopong Roundball','Blagan Road','Bognay Street','Buko Street',
                'Cherry Street','Chico Street','Durian Avenue','E. Dangcolis Street','E. Salamanca Avenue',
                'F. Falalimpa Street','G. Muega Avenue','G. Saliganan Street','General Santos-Polomolok National Highway',
                'J. Sanchez Avenue','Kamatsili Street','Kardaba Street','Labana Street','Lakatan Street',
                'Lemon Street','M. Rufilla Avenue','Mabulo Street','Mangga Avenue','Mangostan Street',
                'Marang Avenue','Marcos Avenue Extension','Melon Street','Morado Street','Mount Manunggal Street',
                'Mount Mayon Street','Mullberry Street','P. Isidro Street','Peras Street','Rizal Avenue',
                'Santol Avenue','Tambis Street',
            ],

            'Fatima' => [
                'Airport Terminal Road','Filipino-American Friendship Avenue','General Santos City Circumferential Road',
                'General Santos-T\'Boli Road',
            ],

            'Labangal' => [
                'Avanza Street','Bombil Street','Calumpang PS Road','Falcon Street','Fifth Road',
                'First Road','Fourth Road','Goldfish Street','Hadano Avenue','Jose P. Rizal Street',
                'Labangal Road','Maguindanao Avenue','Makar-Kiamba Road','Malvar Street','Mars Street',
                'Quilantang Street','Rivera Street','Rizal Avenue','San Miguel Road','Santo Niño Road',
                'Second Road','Swan Street','Tangigui Street','Third Road',
            ],
            'Bula' => [
                'Jorge Royeca Boulevard','Rafael Alunan Street','Catalino Cabe Street','Asali Street','Lucio Congson Street',
                'Honorio Arriola Street','Asai Road','Zone 1-B','Ventura Damicog Street','Zone 9',
                'Pedro Labao Street','Nemesio Sanz Street','Jose Divinagracia Boulevard','Zone 6',
            ],

            'Dadiangas North' => [
                'Abdawa Street','Abelardo Gonzales Street','Abubacar Banisil Street','Acharon Compound','Alunan Extension',
                'Balagtas Street','Bughaw Street','C. M. Recto Street','C. P. Garcia Street','Cagampang Street',
                'Casimiro B. Bañas Street','City Hall Drive','Dalandan Street','Felipe R. Cariño Street','Gervacio Aparente Street',
                'Gregorio Daproza Street','Irineo Santiago Boulevard','J. P. Laurel Avenue','Jamora Road','Jose Catolico, Sr. Avenue',
                'Kasoy Street','Kayumanggi Street','Lapu-Lapu Street','Lukban Street','M. Quezon Avenue',
                'Mandeoya Street','Mansanitas Street','Manuela Jandoc Street','Marist Street','Narangita Street',
                'NDDU Service Road','Niyog Street','Nuestra Señora Dela Paz Street','Osmeña Street','P. Acharon Extension',
                'Pedro Acharon Boulevard','Pendatun Avenue','Piña Street','Pioneer Avenue','President Elpidio Quirino Avenue',
                'President Manuel Roxas Avenue','R. Magsaysay Avenue','Saging Street','Salazar Street','Sampaguita Street',
                'Sampaloc Extension','San Miguel Street','Santol Street','Senator Jose W. Diokno Street','Senior Citizens Street',
                'Sulpicio Muñasque Street','Teodulo Ramirez, Sr. Street','Tieza Street','Udtog Matalam Street','Valente Mercado Street',
                'Veterans Street','Zapote Street',
            ],

            'Dadiangas South' => [
                'Salazar Street','Beatiles Street','Niyog Street','Niyog Exit','Magsaysay Avenue',
                'Cagampang Street','Mansanitas Street','Ferrariz Street','West Side Street','De Dios Street',
                'President Elpidio Quirino Avenue','Gumamela Street','Rosas Street','Nuestra Señora Dela Paz Street',
                'Narangita Street','Casquejo Street',
            ],

            'Mabuhay' => [
                'Golingan Street','Klinan Road','Habitat Road','Purok 3','Purok 4',
                'Purok 9','Albert Morrow Boulevard','Aquino Street','Guinto Street','Mabuhay Road',
                'NGCP Road','Nuñez Street','Silway 8-Upper Labay Road',
            ],
            'Baluan' => [
                'San Francisco','Tahimik','Amethyst Street','Jorge Royeca Boulevard',
                'Leodegario Arradaza Street','Peridot Street','Sarangani-Davao del Sur Coastal Road','Turquoise Street',
            ],

            'Batomelong' => [
                'Pulutana','Purok 2',
            ],

            'Buayan' => [
                'Maligaya','Minanga','San Vicente','Sarif Mucsin',
                'Barangay Buayan Road','Jainal Abedin Street','Jorge Royeca Boulevard','Rajah Muda Avenue',
            ],

            'City Heights' => [
                'Apple Street','Armando Alerta Street','Bayabas Street','E. Bulaong Avenue','Earth Street',
                'Fiscal Gregorio Daproza Avenue','Francisco Balagtas Street','G. Del Pilar Extension','G. Del Pilar Street',
                'Lanzones Street','Mangga Street','Mangosteen Street','Neptune Street','P. Acharon Extension',
                'Pili Street','Santa Cruz Street','Saturn Street','Torres Street','Victorino Velasquez Street','Yusepeng Compound',
            ],
            'Dadiangas East' => [
                'Pres. Jose P. Laurel Avenue','Matalam Street','Senior Citizen Street','Zapote Street','Ramos Street',
                'Pendatun Avenue','Roxas E. Avenue','Pres. Sergio Osmeña Avenue','Camia Street','Sineguelas Street',
                'Casquejo Street','Camachili Street','Kadulasan Street','Donato Quinto Street','Santiago Boulevard',
                'Digos-Makar Road','Champaca Street','Sampaguita Street','Kasoy Street',
            ],

            'Conel' => [
                'Albert Morrow Boulevard','Anthurium Street','Cañas Road','Carnation Street','Chico Street',
                'Daisy Street','Gumamela Street','Ilang Ilang Street','Jagnaan Avenue','Leopoldo D. Dacera Avenue',
                'Lerio Street','Mangga Street','Marigold Street','Melon Street','NLSA Road',
                'Ofrencio Torres Santos Street','Orchids Street','Papaya Avenue','Penpenia Street','Pine Tree Street',
                'Rosal Street','Rose Street','Sampaguita Street','Santan Street','Silway 8-Upper Labay Road',
                'Sinuelas Street','Suha Street','Sunflower Street','Tambis Street','Tulip Street','Waling Waling Street','Water Lily Street',
            ],

            'Dadiangas West' => [
                'P. Acharon Extension','Macopa Extension','Claro M. Recto Street','Lukban Street','Pendatun Avenue',
                'Pres. Ramon Magsaysay Avenue','Atis Street','P. Acharon Boulevard','Purok Silway Fatima Road','Darimco Street',
                'Silway Road','Papaya Street',
            ],

            'Katangawan' => [
                'Albert Morrow Boulevard','Antigua Street','Apayo Street','Buttercup Street','Caicos Street',
                'Carinal Street','Chrysanthemum Street','Conel Road','Daffodil Street','Diversion Road',
                'Jamaica Street','Lavender Street','Lilac Street','Lucio Velayo, Sr. Road','Magnolia Street','Peony Street',
            ],
            'Ligaya' => [
                'A. Sorilla Street','Ancheta Street','Antonino Street','Cadorna Street','Carinal Street','Cunanan Street',
                'Datu Abdula Street','France Street','Greece Street','Italy Street','Japan Street','Narra Street','Spain Street','Tapel Avenue',
            ],

            'Olympog' => [
                'Balakayo','Balsinang','Purok 1','Purok 2','Atis Street','Avocado Street','Ayano Street','Balimbing Street',
                'Bayabas Street','Bual Street','Cagampang Street','Caimito Street','Conel Road','Cuñado Street','Kamansi Street',
                'Kasoy Street','Lagare Avenue','Langka Street','Manga Street','Mansanitas Street','Niyog Street','Paña Street',
                'Papaya Street','Penpenia Street','Pomperada Street','Sampaloc Street','Santol Street','Silway 8-Upper Labay Road',
                'Tambis Street',
            ],

            'San Isidro' => [
                '1st Corner','1st Street','2nd Corner','2nd Street','3rd Street','4th Street','5th Street','6th Street','7th Street',
                'Adam Street','Antigua Street','Aruba Street','Bahamas Street','Barbados Street','Bequia Street','Block 3','Block 4',
                'Bonaire Street','Bougainvillea Street','Camella Road','Cayman Street','Dole Avenue','Galilee Street','Georgetown Street',
                'Gervacio Aparente Street','Gonave Street','Guinintuan Street','Guinto Street','Jose Rizal Street','Leon Llido Street',
                'Leopoldo D. Dacera Avenue','Lilac Street','Mauro Y. Lacap Street','Montserrat Street','Nevis Street','NLSA Road',
                'Nuñez Street','Odi Compound 2','Pacubilla Street','Perez Avenue','Pula Street','Purok Kauswagan-Foremost 2 Road',
                'Rafael A. Salvani, Sr. Street','Rosas Street','Saint Anthony Street','Saint Dominic Street','Saint Elizabeth Street',
                'Saint Gabriel Street','Saint John Street','Saint Joseph Street','Saint Luke Street','Saint Mark Street','Saint Martha Street',
                'Saint Martin Street','Saint Matthew Street','Saint Michael Street','Saint Raphael Street','Saint Thomas Street',
                'Saint Vincent Street','Saints Peter and Paul Street','San Francisco Street','San Juanico Drive','San Lorenzo Street',
                'Santa Ana Street','Santa Anna Street','Santa Cecilla Street','Santa Maria Street','Santa Veronica Street','Santo Niño Street',
                'Santo Rosario Street','Sebastian Street','Solarte Street','Spring Circle','Trinidad Street','Turks Street','Velligas Street','Yumang Street',
            ],

            'San Jose' => [
                'Blagan Road','General Santos-T\'Boli Road',
            ],
            'Siguel' => [
                'Changco','Seguil','Makar-Kiamba Road','Sarangani-Sultan Kudarat Coastal Road',
            ],

            'Sinawal' => [
                'Cabuay','Albert Morrow Boulevard','Ayuste Avenue','Candor Street','Conduct Street','Cornelio Avenue','Courage Street',
                'Decency Street','Gorgoya Avenue','Honest Street','Industry Street','J. Sanchez Avenue','J.Lagon Street','Lomboy Street',
                'M. Legaspi Street','Marcos Avenue','Marcos Avenue Extension','Nses Drive','Resolute Street','Rizal Avenue','Road 1',
                'Road 2','Road 3','Road 4','Road 5','Road 6','Rosas Street','Sampaguita Street','San Jose-Sinawal Road','Santan Street',
                'Sincere Street',
                ],

            'Tambler' => [
                'General Santos City Circumferential Road','Makar-Kiamba Road',
            ],

            'Upper Labay' => [
                'Daan Banwang','Kidam','Lower Labay','Purok 1','Purok 2','Purok 6','Silway 8-Upper Labay Road',
            ],
        ];

        // Build rows for DB insert
        $rows = [];
        foreach ($data as $barangay => $streets) {
            foreach ($streets as $street) {
                $rows[] = [
                    'barangay'   => $barangay,
                    'street'     => $street,
                    'address'    => trim("{$street}, Barangay {$barangay}, General Santos City"),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert in chunks to be safe if the list is large
        $chunks = array_chunk($rows, 200);
        foreach ($chunks as $chunk) {
            DB::table('locations')->insert($chunk);
        }

        $this->command->info('Inserted ' . count($rows) . ' General Santos City sample locations.');
    }
}
