<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Option;
use App\Models\Brand;

class VintedCatalogSeeder extends Seeder
{
    protected $dataIdx = 0;

    // Translation Dictionary (English -> French), we will swap it to find English names
    protected $translations = [
        "Women" => "Femmes",
        "Clothing" => "Vêtements",
        "Shoes" => "Chaussures",
        "Bags" => "Sacs",
        "Accessories" => "Accessoires",
        "Beauty" => "Beauté",
        "Men" => "Hommes",
        "Grooming" => "Soins",
        "Kids" => "Enfants",
        "Girls clothing" => "Vêtements filles",
        "Boys clothing" => "Vêtements garçons",
        "Toys" => "Jeux & Jouets",
        "School supplies" => "Fournitures scolaires",
        "Furniture & decor" => "Mobilier & Décoration",
        "Pushchairs, carriers & car seats" => "Poussettes, porte-bébés & sièges auto",
        "Bathing & changing" => "Bain & change",
        "Childproofing & safety equipment" => "Sécurité",
        "Health & pregnancy" => "Santé & Grossesse",
        "Nursing & feeding" => "Repas & allaitement",
        "Sleep & bedding" => "Sommeil",
        "Other kids' items" => "Autres articles enfants",

        "Jumpers & sweaters" => "Pulls & sweats",
        "Dresses" => "Robes",
        "Tops & t-shirts" => "Hauts & t-shirts",
        "Trousers & leggings" => "Pantalons & leggings",
        "Jumpsuits & playsuits" => "Combinaisons",
        "Lingerie & nightwear" => "Lingerie & pyjamas",
        "Activewear" => "Sport",
        "Other clothing" => "Autres vêtements",
        "Outerwear" => "Manteaux & vestes",
        "Suits & blazers" => "Costumes & blazers",
        "Skirts" => "Jupes",
        "Jeans" => "Jeans",
        "Shorts & cropped trousers" => "Shorts & pantacourts",
        "Swimwear" => "Maillots de bain",
        "Maternity clothes" => "Vêtements de maternité",
        "Costumes & special outfits" => "Déguisements & tenues spéciales",

        "Ballerinas" => "Ballerines",
        "Boat shoes, loafers & moccasins" => "Chaussures bateau & mocassins",
        "Clogs & mules" => "Sabots & mules",
        "Flip-flops & slides" => "Tongs & claquettes",
        "Lace-up shoes" => "Chaussures à lacets",
        "Sandals" => "Sandales",
        "Sports shoes" => "Chaussures de sport",
        "Boots" => "Bottes",
        "Espadrilles" => "Espadrilles",
        "Heels" => "Talons",
        "Mary Janes & T-bar shoes" => "Babies & salomés",
        "Slippers" => "Chaussons",
        "Trainers" => "Baskets",

        "Beach bags" => "Sacs de plage",
        "Bucket bags" => "Sacs seau",
        "Clutches" => "Pochettes",
        "Gym bags" => "Sacs de sport",
        "Hobo bags" => "Sacs hobo",
        "Luggage & suitcases" => "Bagages & valises",
        "Satchels & messenger bags" => "Cartables & besaces",
        "Tote bags" => "Sacs cabas",
        "Wristlets" => "Pochettes dragonne",
        "Backpacks" => "Sacs à dos",
        "Briefcases" => "Serviettes & porte-documents",
        "Bum bags" => "Bananes",
        "Garment bags" => "Housses à vêtements",
        "Handbags" => "Sacs à main",
        "Holdalls & duffel bags" => "Sacs de voyage",
        "Makeup bags" => "Trousses de toilette",
        "Shoulder bags" => "Sacs bandoulière",
        "Wallets & purses" => "Portefeuilles & porte-monnaie",

        "Hair accessories" => "Accessoires cheveux",
        "Hats & caps" => "Chapeaux & casquettes",
        "Keyrings" => "Porte-clés",
        "Sunglasses" => "Lunettes de soleil",
        "Watches" => "Montres",
        "Bandanas & headscarves" => "Bandanas & foulards",
        "Belts" => "Ceintures",
        "Gloves" => "Gants",
        "Handkerchiefs" => "Mouchoirs",
        "Jewellery" => "Bijoux",
        "Scarves & shawls" => "Echarpes & châles",
        "Umbrellas" => "Parapluies",
        "Other accessories" => "Autres accessoires",

        "Perfume" => "Parfums",
        "Beauty tools" => "Accessoires beauté",
        "Nail care" => "Manucure",
        "Hair care" => "Soins cheveux",
        "Make-up" => "Maquillage",
        "Facial care" => "Soins visage",
        "Hand care" => "Soins mains",
        "Body care" => "Soins corps",
        "Other beauty items" => "Autres produits de beauté",
        "Trousers" => "Pantalons",
        "Socks & underwear" => "Chaussettes & sous-vêtements",
        "Shorts" => "Shorts",
        "Sleepwear" => "Nuit",
        "Other men's clothing" => "Autres vêtements hommes",
        "Jumpers & hoodies" => "Pulls & sweats",
        "Formal shoes" => "Chaussures de ville",
        "Bags & backpacks" => "Sacs & sacs à dos",
        "Braces" => "Bretelles",
        "Ties & bow ties" => "Cravates & nœuds papillon",
        "Pocket squares" => "Pochettes de costume",
        "Tools & accessories" => "Accessoires rasage",
        "Aftershave & cologne" => "Après-rasage & eau de cologne",
        "Grooming kits" => "Trousses de toilette",
        "Hand & nail care" => "Soins mains & ongles",
        "Other grooming items" => "Autres produits de soins",
        "Trousers, shorts & dungarees" => "Pantalons, shorts & salopettes",
        "Baby girls' clothing" => "Vêtements bébé fille",
        "Clothing bundles" => "Lots de vêtements",
        "Fancy dress & costumes" => "Déguisements",
        "Formal wear & special occasion clothing" => "Tenues de cérémonie",
        "Clothing for twins" => "Vêtements jumeaux",
        "Other girls' clothing" => "Autres vêtements fille",
        "Baby boys' clothing" => "Vêtements bébé garçon",
        "Other boys' clothing" => "Autres vêtements garçon",
        "Soft toys & stuffed animals" => "Peluches & doudous",
        "Educational toys" => "Jeux éducatifs",
        "Baby activities & toys" => "Jouets d'éveil",
        "Outdoor & sports toys" => "Jeux d'extérieur",
        "Action figures & accessories" => "Figurines & accessoires",
        "Construction toys" => "Jeux de construction",
        "Dolls & accessories" => "Poupées & accessoires",
        "Arts & crafts" => "Loisirs créatifs",
        "Electronic toys" => "Jeux électroniques",
        "Vehicles & tracks" => "Petites voitures & circuits",
        "Role play" => "Jeux d'imitation",
        "Board games & puzzles" => "Jeux de société & puzzles",
        "Musical toys" => "Instruments de musique",
        "Other toys" => "Autres jeux",
        "School bags" => "Cartables",
        "Lunch boxes & bags" => "Boîtes à goûter",
        "Nursery furniture" => "Mobilier bébé",
        "Decor & keepsakes" => "Décoration & souvenirs",
        "Playmats & padded flooring" => "Tapis d'éveil",
        "Playpens" => "Parcs",
        "Play furniture" => "Mobilier enfants",
        "Rugs & mats" => "Tapis",
        "Kids' mattresses" => "Matelas enfants",
        "Storage & organization" => "Rangement",
        "Other kids' furniture" => "Autres mobiliers",
        "Buggies & pushchairs" => "Poussettes",
        "Car seats" => "Sièges auto",
        "Baby carriers & wraps" => "Porte-bébés & écharpes",
        "Car seat accessories" => "Accessoires sièges auto",
        "Buggy accessories" => "Accessoires poussette",
        "Booster seats" => "Rehausseurs",
        "Baby changing bags" => "Sacs à langer",
        "Changing mats & covers" => "Matelas & housses à langer",
        "Nappy storage & disposal" => "Rangement couches & poubelles",
        "Skincare & hygiene" => "Soins & hygiène",
        "Step stools" => "Marchepieds",
        "Bathing" => "Bain",
        "Nappies" => "Couches",
        "Potties" => "Pots",
        "Baby gates & guards" => "Barrières de sécurité",
        "Hearing protection" => "Casques antibrouits",
        "Childproofing accessories" => "Accessoires sécurité",
        "Safety harnesses & reins" => "Harnais de sécurité",
        "Humidifiers" => "Humidificateurs",
        "Postpartum care" => "Soins post-partum",
        "Pregnancy support belts" => "Ceintures de grossesse",
        "Thermometers" => "Thermomètres",
        "Nasal aspirators" => "Mouche-bébés",
        "Pregnancy pillows" => "Coussins de maternité",
        "Scales" => "Pèse-bébés",
        "Baby monitors" => "Babyphones",
        "Bedding, blankets & throws" => "Linge de lit & couvertures",
        "Heating pads & hot water bottles" => "Bouillottes",
        "Sleep sacks & wearable blankets" => "Gigoteuses",
        "Swaddles" => "Draps d'emmaillotage",
        "Bed rails & guards" => "Barrières de lit",
        "Blackout shades" => "Rideaux occultants",
        "Nightlights & wake-up lights" => "Veilleuses",
        "Sleeping bags" => "Duvets",
        "White noise machines" => "Bruits blancs",
        "Baby food blenders & makers" => "Robots & mixeurs",
        "Bottle feeding" => "Biberons & accessoires",
        "Cups, dishes & utensils" => "Vaisselle & couverts",
        "Dummies & soothers" => "Sucettes & anneaux de dentition",
        "High chairs" => "Chaises hautes",
        "Muslins & burp cloths" => "Langes",
        "Bibs" => "Bavoirs",
        "Breastfeeding" => "Allaitement",
        "Feeding pillows & covers" => "Coussins d'allaitement",
        "Dummy accessories" => "Attache-sucettes",
        "High chair accessories" => "Accessoires chaises hautes",
        "Sterilisers" => "Stérilisateurs"
    ];

    protected $frToEnMap = [];
    protected $sizeAttributes = []; // Cache for size attributes

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M'); // Increase memory limit for big JSONs

        $dataPath = base_path('DATA');
        if (!File::exists($dataPath)) {
            $this->command->error("DATA directory not found at: $dataPath");
            return;
        }

        // Build Reverse Map
        $this->frToEnMap = array_flip($this->translations);

        try {
            DB::beginTransaction();

            // CLEANUP: Truncate tables to avoid duplicates (specifically categories with null vinted_id vs new ones)
            // Disable Foreign Key Checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->command->info('Truncating tables...');
            DB::table('categories')->truncate();
            DB::table('brands')->truncate();
            DB::table('attributes')->truncate();
            DB::table('options')->truncate();
            DB::table('attribute_category')->truncate();
            // Be careful with products linkages if products exist. 
            // If we truncate attributes, attribute_product might be invalid.
            // For now, assuming "reset catalog" means resetting these.

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 1. Seed Brands
            $this->command->info('Seeding Brands...');
            $this->seedBrands($dataPath);

            // 2. Seed Colors
            $this->command->info('Seeding Colors...');
            $this->seedColors($dataPath);

            // 3. Seed Sizes
            $this->command->info('Seeding Sizes...');
            $this->seedSizes($dataPath);

            // 4. Seed Categories
            $this->command->info('Seeding Categories...');
            $this->seedCategories($dataPath);

            DB::commit();
            $this->command->info('Vinted Catalog Seeded Successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Seeder Failed: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }

    private function seedBrands($path)
    {
        $file = $path . '/brand.json';
        if (!File::exists($file))
            return;

        $data = json_decode(File::get($file), true);

        // Filter top brands by item_count to avoid spamming 40k brands
        // Or specific list. Let's take top 2000 popular brands.
        usort($data, function ($a, $b) {
            return $b['item_count'] <=> $a['item_count'];
        });

        $topBrands = array_slice($data, 0, 2000);

        foreach ($topBrands as $brand) {
            \App\Models\Brand::updateOrCreate(
                ['name' => $brand['title']],
                ['slug' => $brand['slug']] // Usually unique, but updateOrCreate handles match
            );
        }
    }

    private function seedColors($path)
    {
        $file = $path . '/color.json';
        if (!File::exists($file))
            return;

        $data = json_decode(File::get($file), true);

        $attr = Attribute::firstOrCreate(
            ['name' => 'Couleurs'],
            ['code' => 'colors', 'type' => 'color', 'icon' => 'fa-palette']
        );

        foreach ($data as $item) {
            // Use code (e.g. BLACK) converted to Title Case (Black) for consistency
            $colorName = ucfirst(strtolower($item['code'] ?? $item['title']));

            Option::firstOrCreate(
                ['attribute_id' => $attr->id, 'value' => $colorName]
                // We are not storing Hex for now as Option table doesn't support it.
                // If needed, we would need to add 'meta' or 'color_hex' column.
            );
        }
    }
    private function seedSizes($path)
    {
        $file = $path . '/size.json';
        if (!File::exists($file))
            return;

        $data = json_decode(File::get($file), true);

        // Group by 'description' or 'caption' to create Attributes
        // File structure: { id, description: "Women (UK)", sizes: [...] }

        foreach ($data as $group) {
            if (empty($group['sizes']))
                continue;

            $groupName = $group['description'] ?? $group['caption'] . ' ' . $group['id'];

            // Map Vinted group descriptions to clean Attribute names
            $attrName = match ($group['description']) {
                'Women (UK)', 'Femme (UK)' => 'Taille Femme',
                'Men (UK)', 'Homme (UK)' => 'Taille Homme',
                'Shoes, women, UK' => 'Chaussures Femme',
                'Shoes, men, UK' => 'Chaussures Homme',
                default => $groupName
            };

            // Create Attribute
            // Generate a code from ID to be unique
            $attr = Attribute::firstOrCreate(
                ['name' => $attrName],
                ['code' => 'size_group_' . $group['id'], 'type' => 'select']
            );

            $this->sizeAttributes[$group['id']] = $attr;

            foreach ($group['sizes'] as $size) {
                Option::firstOrCreate(
                    ['attribute_id' => $attr->id, 'value' => $size['title']]
                );
            }
        }
    }

    private function seedCategories($path)
    {
        $file = $path . '/catalog.json';
        if (!File::exists($file))
            return;

        $data = json_decode(File::get($file), true);

        // Custom Order Priorities
        $priority = [
            'Femmes' => 1,
            'Hommes' => 2,
            'Enfants' => 3
        ];

        foreach ($data as $cat) {
            // FILTER: Remove 'Maison'
            if ($cat['title'] === 'Maison') {
                continue;
            }

            // ORDER: Enforce Women > Men > Kids
            if (isset($priority[$cat['title']])) {
                $cat['order'] = $priority[$cat['title']];
            } else {
                $cat['order'] = 100 + ($cat['order'] ?? 0);
            }

            $this->processCategoryNode($cat, null);
        }
    }

    private function processCategoryNode($node, $parentId)
    {
        // 1. Determine English Name
        $frenchTitle = $node['title'];
        $englishName = $this->frToEnMap[$frenchTitle] ?? $frenchTitle; // Fallback to French if no mapping

        // 2. Create/Update Category
        $category = Category::updateOrCreate(
            ['vinted_id' => $node['id']],
            [
                'name' => $englishName,
                'name_fr' => $frenchTitle,
                'slug' => Str::slug($englishName . '-' . $node['id']), // Unique slug
                'parent_id' => $parentId,
                'order' => $node['order'] ?? 0,
                // 'image' => $node['photo']['url'] ?? null, // Optional: seed image
            ]
        );

        // 3. Attach Attributes (Sizes)
        // json has "size_group_ids": [4, 7, ...]
        if (isset($node['size_group_ids']) && is_array($node['size_group_ids'])) {
            $attrIds = [];
            foreach ($node['size_group_ids'] as $groupId) {
                if (isset($this->sizeAttributes[$groupId])) {
                    $attrIds[] = $this->sizeAttributes[$groupId]->id;
                }
            }
            if (!empty($attrIds)) {
                $category->assignedAttributes()->syncWithoutDetaching($attrIds);
            }
        }

        // 4. Attach Colors (Global)
        // If it's a leaf node or specific depth? 
        // Vinted data "color_field_visibility": 1
        if (isset($node['color_field_visibility']) && $node['color_field_visibility'] == 1) {
            $colorAttr = Attribute::where('code', 'colors')->first();
            if ($colorAttr) {
                $category->assignedAttributes()->syncWithoutDetaching([$colorAttr->id]);
            }
        }

        // 5. Recurse (catalogs array)
        if (isset($node['catalogs']) && is_array($node['catalogs'])) {
            foreach ($node['catalogs'] as $child) {
                $this->processCategoryNode($child, $category->id);
            }
        }
    }
}
