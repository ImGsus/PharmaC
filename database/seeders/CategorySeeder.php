<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['name' => 'Medicines', 'description' => 'Prescription Medicines, Over-the-Counter (OTC) Medicines, Generic Medicines, Branded Medicines, Pediatric Medicines, Topical Medicines, etc.', 'fixed_key' => 'medicines'],
            ['name' => 'Vitamins & Supplements', 'description' => 'Vitamins, Minerals, Food Supplements, Herbal Supplements, Nutritional Supplements, etc.', 'fixed_key' => 'vitamins-supplements'],
            ['name' => 'Medical Supplies', 'description' => 'Bandages, Gauze, Cotton, Syringes, Medical Tape, Gloves, Masks, Thermometers, etc.', 'fixed_key' => 'medical-supplies'],
            ['name' => 'Personal Care & Hygiene', 'description' => 'Soap, Shampoo, Conditioner, Deodorant, Hand Sanitizer, Wet Wipes, Tissues, Feminine Hygiene Products, etc.', 'fixed_key' => 'personal-care-hygiene'],
            ['name' => 'Dental Care', 'description' => 'Toothbrushes, Toothpaste, Mouthwash, Dental Floss, Denture Care Products, etc.', 'fixed_key' => 'dental-care'],
            ['name' => 'Baby & Mother Care', 'description' => 'Baby Diapers, Baby Wipes, Baby Formula, Baby Bottles, Pacifiers, Baby Soap, Baby Shampoo, Maternity Products, etc.', 'fixed_key' => 'baby-mother-care'],
            ['name' => 'Skin & Hair Care', 'description' => 'Facial Care Products, Moisturizers, Sunscreen, Lotions, Acne Care, Hair Care Products, etc.', 'fixed_key' => 'skin-hair-care'],
            ['name' => 'First Aid', 'description' => 'First Aid Kits, Antiseptics, Alcohol, Wound Care Products, Burn Care Products, etc.', 'fixed_key' => 'first-aid'],
            ['name' => 'Medical Devices', 'description' => 'Blood Pressure Monitors, Blood Glucose Meters, Nebulizers, Pulse Oximeters, Medical Scales, etc.', 'fixed_key' => 'medical-devices'],
            ['name' => 'Food & Beverages', 'description' => 'Bottled Water, Biscuits, Snacks, Health Drinks, Nutritional Drinks, etc.', 'fixed_key' => 'food-beverages'],
            ['name' => 'Household & Other', 'description' => 'Disinfectants, Cleaning Products, Insect Repellents, Household Items, and products that do not fit another category.', 'fixed_key' => 'household-other'],
        ];

        foreach ($items as $it) {
            Category::updateOrCreate(
                ['fixed_key' => $it['fixed_key']],
                ['name' => $it['name'], 'description' => $it['description'], 'fixed_key' => $it['fixed_key']]
            );
        }
    }
}
