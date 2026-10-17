<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class NordCafeSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // ==================== 7.1 MENU MINUMAN ====================
            [
                'category' => [
                    'name' => 'Non Coffee',
                    'slug' => 'non-coffee',
                    'type' => 'minuman',
                    'icon' => 'fa-blender',
                ],
                'products' => [
                    ['name' => 'Matcha', 'price' => 23000, 'tags' => 'Bestseller,Non-Coffee', 'calories' => 180, 'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Redvelvet', 'price' => 21000, 'tags' => 'Sweet,Favorite', 'calories' => 220, 'image_url' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Tiramisu', 'price' => 21000, 'tags' => 'Dessert Drink', 'calories' => 230, 'image_url' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Cookies & Cream', 'price' => 21000, 'tags' => 'Popular,Kids', 'calories' => 260, 'image_url' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Taro', 'price' => 21000, 'tags' => 'Sweet,Creamy', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Vanilla', 'price' => 21000, 'tags' => 'Classic,Smooth', 'calories' => 190, 'image_url' => 'https://images.unsplash.com/photo-1558857563-b37fcbfca605?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Choco Oreo', 'price' => 21000, 'tags' => 'Bestseller', 'calories' => 270, 'image_url' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chocolate Pistacio', 'price' => 21000, 'tags' => 'Nutty,Special', 'calories' => 240, 'image_url' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Thai Tea', 'price' => 21000, 'tags' => 'Classic,Favorite', 'calories' => 200, 'image_url' => 'https://images.unsplash.com/photo-1558857563-b37fcbfca605?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Green Tea', 'price' => 21000, 'tags' => 'Fresh,Tea', 'calories' => 160, 'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Bubble Gum', 'price' => 21000, 'tags' => 'Sweet,Fun', 'calories' => 200, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chocolate', 'price' => 21000, 'tags' => 'Classic,Rich', 'calories' => 230, 'image_url' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Choco Silverking', 'price' => 21000, 'tags' => 'Specialty', 'calories' => 260, 'image_url' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Milo Saurus', 'price' => 21000, 'tags' => 'Bestseller', 'calories' => 280, 'image_url' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Air Mineral', 'price' => 6000, 'tags' => 'Fresh Water', 'calories' => 0, 'image_url' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Yaqult Series',
                    'slug' => 'yaqult-series',
                    'type' => 'minuman',
                    'icon' => 'fa-glass-citrus',
                ],
                'products' => [
                    ['name' => 'Mango Yaqult', 'price' => 22000, 'tags' => 'Bestseller,Fresh', 'calories' => 140, 'image_url' => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Mandarin Yaqult', 'price' => 22000, 'tags' => 'Citrus,Fresh', 'calories' => 135, 'image_url' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Banana Yaqult', 'price' => 22000, 'tags' => 'Sweet,Fresh', 'calories' => 150, 'image_url' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Lychee Yaqult', 'price' => 22000, 'tags' => 'Bestseller', 'calories' => 140, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Peach Yaqult', 'price' => 22000, 'tags' => 'Refreshing', 'calories' => 138, 'image_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Tea Series',
                    'slug' => 'tea-series',
                    'type' => 'minuman',
                    'icon' => 'fa-leaf',
                ],
                'products' => [
                    ['name' => 'Lychee Tea', 'price' => 20000, 'tags' => 'Bestseller,Tea', 'calories' => 95, 'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Lemon Tea', 'price' => 20000, 'tags' => 'Classic,Fresh', 'calories' => 80, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Strawberry Tea', 'price' => 20000, 'tags' => 'Fruity,Fresh', 'calories' => 90, 'image_url' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Basic Coffee',
                    'slug' => 'basic-coffee',
                    'type' => 'minuman',
                    'icon' => 'fa-coffee',
                ],
                'products' => [
                    ['name' => 'Espresso', 'price' => 16000, 'tags' => 'Classic,Hot', 'calories' => 5, 'image_url' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Americano', 'price' => 21000, 'tags' => 'Bestseller,Coffee', 'calories' => 10, 'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Peach Americano', 'price' => 23000, 'tags' => 'Refreshing,Specialty', 'calories' => 65, 'image_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Aligator (Sparkling)', 'price' => 23000, 'tags' => 'Sparkling,Signature', 'calories' => 70, 'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Caffe Latte', 'price' => 23000, 'tags' => 'Popular,Classic', 'calories' => 150, 'image_url' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Cappucino', 'price' => 23000, 'tags' => 'Classic,Favorite', 'calories' => 130, 'image_url' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Mochaccino', 'price' => 23000, 'tags' => 'Sweet,Choco', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Signature',
                    'slug' => 'signature',
                    'type' => 'minuman',
                    'icon' => 'fa-mug-hot',
                ],
                'products' => [
                    ['name' => 'Pistachio Latte', 'price' => 23000, 'tags' => 'Bestseller,Signature', 'calories' => 220, 'image_url' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Nord Palm Sugar', 'price' => 23000, 'tags' => 'Bestseller,Signature', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Butterscotch Latte', 'price' => 23000, 'tags' => 'Sweet,Favorite', 'calories' => 230, 'image_url' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Hazelnut Latte', 'price' => 23000, 'tags' => 'Nutty,Favorite', 'calories' => 200, 'image_url' => 'https://images.unsplash.com/photo-1529892485617-25f63cd7b1e9?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Vanilla Latte', 'price' => 23000, 'tags' => 'Classic,Smooth', 'calories' => 190, 'image_url' => 'https://images.unsplash.com/photo-1572442388796-11668ba67e53?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Caramel Latte', 'price' => 23000, 'tags' => 'Popular', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1599398054066-846f28917f38?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Caramel Machiato', 'price' => 23000, 'tags' => 'Favorite', 'calories' => 230, 'image_url' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Banana Latte', 'price' => 23000, 'tags' => 'Unique,New', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Salted Caramel', 'price' => 23000, 'tags' => 'Must Try,Bestseller', 'calories' => 225, 'image_url' => 'https://images.unsplash.com/photo-1582293041079-7814c2f12063?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Avocado Coffee', 'price' => 23000, 'tags' => 'Creamy,Signature', 'calories' => 260, 'image_url' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Spanish Latte', 'price' => 23000, 'tags' => 'Rich,Creamy', 'calories' => 240, 'image_url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'By Glasses',
                    'slug' => 'by-glasses',
                    'type' => 'minuman',
                    'icon' => 'fa-glass-water',
                ],
                'products' => [
                    ['name' => 'Mont Blanc', 'price' => 25000, 'tags' => 'Specialty,Glass', 'calories' => 240, 'image_url' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'ButterScotch Salt Latte', 'price' => 25000, 'tags' => 'Specialty,Favorite', 'calories' => 250, 'image_url' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Nordic Latte', 'price' => 23000, 'tags' => 'House Blend', 'calories' => 210, 'image_url' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'By Bottle',
                    'slug' => 'by-bottle',
                    'type' => 'minuman',
                    'icon' => 'fa-bottle-droplet',
                ],
                'products' => [
                    ['name' => '1000 ML Nord Palm Sugar', 'price' => 85000, 'tags' => 'Share,Bottle', 'calories' => 850, 'image_url' => 'https://images.unsplash.com/photo-1558857563-b37fcbfca605?auto=format&fit=crop&w=600&q=80'],
                    ['name' => '500 ML Nord Palm Sugar', 'price' => 45000, 'tags' => 'Bottle,Favorite', 'calories' => 425, 'image_url' => 'https://images.unsplash.com/photo-1558857563-b37fcbfca605?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Manual Brew',
                    'slug' => 'manual-brew',
                    'type' => 'minuman',
                    'icon' => 'fa-flask',
                ],
                'products' => [
                    ['name' => 'Nord V60', 'price' => 25000, 'tags' => 'Manual Brew,Artisan', 'calories' => 5, 'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Japanese', 'price' => 25000, 'tags' => 'Iced,Fresh', 'calories' => 5, 'image_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Vietnam Drip', 'price' => 21000, 'tags' => 'Tradisional', 'calories' => 180, 'image_url' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Kopi Tubruk', 'price' => 17000, 'tags' => 'Authentic', 'calories' => 5, 'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80'],
                ]
            ],

            // ==================== 7.2 MENU MAKANAN ====================
            [
                'category' => [
                    'name' => 'Premium Steak',
                    'slug' => 'premium-steak',
                    'type' => 'makanan',
                    'icon' => 'fa-drumstick-bite',
                ],
                'products' => [
                    ['name' => 'Tenderloin Wagyu 200gr', 'price' => 69000, 'tags' => 'Premium,Wagyu', 'calories' => 520, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Sirloin Wagyu 200gr', 'price' => 66000, 'tags' => 'Premium,Wagyu', 'calories' => 550, 'image_url' => 'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Ori 200gr', 'price' => 39000, 'tags' => 'Grilled,Protein', 'calories' => 430, 'image_url' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Steak Original',
                    'slug' => 'steak-original',
                    'type' => 'makanan',
                    'icon' => 'fa-drumstick-bite',
                ],
                'products' => [
                    ['name' => 'Sirloin Ori', 'price' => 33000, 'tags' => 'Steak,Favorite', 'calories' => 480, 'image_url' => 'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Tenderloin Ori', 'price' => 35000, 'tags' => 'Steak,Tender', 'calories' => 460, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Steak Crispy',
                    'slug' => 'steak-crispy',
                    'type' => 'makanan',
                    'icon' => 'fa-drumstick-bite',
                ],
                'products' => [
                    ['name' => 'Sirloin Crispy', 'price' => 27000, 'tags' => 'Crispy,Bestseller', 'calories' => 510, 'image_url' => 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Tenderloin Crispy', 'price' => 28000, 'tags' => 'Crispy,Tender', 'calories' => 500, 'image_url' => 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Crispy', 'price' => 25000, 'tags' => 'Crispy,Popular', 'calories' => 480, 'image_url' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Rice Bowl',
                    'slug' => 'rice-bowl',
                    'type' => 'makanan',
                    'icon' => 'fa-bowl-rice',
                ],
                'products' => [
                    ['name' => 'Chicken Katsu', 'price' => 20000, 'tags' => 'Bestseller,Crispy', 'calories' => 520, 'image_url' => 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Teriyaki', 'price' => 20000, 'tags' => 'Savory', 'calories' => 490, 'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Blackpaper', 'price' => 20000, 'tags' => 'Spicy', 'calories' => 480, 'image_url' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Barbeque', 'price' => 20000, 'tags' => 'Smoky,BBQ', 'calories' => 510, 'image_url' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Wings Ori', 'price' => 25000, 'tags' => 'Crispy', 'calories' => 560, 'image_url' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Wings Spicy', 'price' => 27000, 'tags' => 'Spicy,Hot', 'calories' => 580, 'image_url' => 'https://images.unsplash.com/photo-1527477321055-43615b65171d?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Wings Sambal', 'price' => 27000, 'tags' => 'Pedas,Favorite', 'calories' => 590, 'image_url' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Beef Slice Blackpaper', 'price' => 32000, 'tags' => 'Beef,Bestseller', 'calories' => 540, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Beef Slice Barbeque', 'price' => 32000, 'tags' => 'Beef,Savory', 'calories' => 550, 'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Beef Slice Sambal B', 'price' => 32000, 'tags' => 'Beef,Pedas', 'calories' => 560, 'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Baso Aci',
                    'slug' => 'baso-aci',
                    'type' => 'makanan',
                    'icon' => 'fa-bowl-food',
                ],
                'products' => [
                    ['name' => 'Baso Aci Ori', 'price' => 19000, 'tags' => 'Gurih,Pedas', 'calories' => 380, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Baso Aci Standard', 'price' => 21000, 'tags' => 'Favorite', 'calories' => 420, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Baso Aci Urat', 'price' => 23000, 'tags' => 'Beef,Bestseller', 'calories' => 460, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Baso Aci Mercon', 'price' => 23000, 'tags' => 'Super Pedas,Hot', 'calories' => 450, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Nasi Goreng',
                    'slug' => 'nasi-goreng',
                    'type' => 'makanan',
                    'icon' => 'fa-plate-wheat',
                ],
                'products' => [
                    ['name' => 'Nasgor Kampung', 'price' => 26000, 'tags' => 'Tradisional,Classic', 'calories' => 550, 'image_url' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Nasgor Nord', 'price' => 26000, 'tags' => 'Signature,Bestseller', 'calories' => 580, 'image_url' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Ayam Goreng',
                    'slug' => 'ayam-goreng',
                    'type' => 'makanan',
                    'icon' => 'fa-drumstick-bite',
                ],
                'products' => [
                    ['name' => 'Ayam Goreng Laos', 'price' => 31000, 'tags' => 'Bestseller,Tradisional', 'calories' => 610, 'image_url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Pasta Series',
                    'slug' => 'pasta-series',
                    'type' => 'makanan',
                    'icon' => 'fa-utensils',
                ],
                'products' => [
                    ['name' => 'Spaghetti Bolognese', 'price' => 29000, 'tags' => 'Classic,Beef', 'calories' => 480, 'image_url' => 'https://images.unsplash.com/photo-1621996346565-e3d5d6281691?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Spaghetti Carbonara', 'price' => 29000, 'tags' => 'Creamy,Favorite', 'calories' => 540, 'image_url' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Spaghetti Aglio Olio', 'price' => 29000, 'tags' => 'Spicy,Aromatic', 'calories' => 420, 'image_url' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Snack',
                    'slug' => 'snack',
                    'type' => 'makanan',
                    'icon' => 'fa-cookie-bite',
                ],
                'products' => [
                    ['name' => 'Kentang Goreng Ori', 'price' => 16000, 'tags' => 'Crispy,Snack', 'calories' => 310, 'image_url' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Kentang Bucket', 'price' => 18000, 'tags' => 'Sharing', 'calories' => 450, 'image_url' => 'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Roti Maryam Ori', 'price' => 16000, 'tags' => 'Sweet', 'calories' => 290, 'image_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Roti Maryam Coklat', 'price' => 17000, 'tags' => 'Chocolate', 'calories' => 340, 'image_url' => 'https://images.unsplash.com/photo-1484723091739-30a097e8f929?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Roti Maryam Coklat Keju', 'price' => 18000, 'tags' => 'Favorite', 'calories' => 380, 'image_url' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Roti Bakar Coklat', 'price' => 16000, 'tags' => 'Sweet', 'calories' => 320, 'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Roti Bakar Coklat Keju', 'price' => 18000, 'tags' => 'Bestseller', 'calories' => 370, 'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Pisang Bakar Coklat', 'price' => 16000, 'tags' => 'Tradisional', 'calories' => 260, 'image_url' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Pisang Bakar Coklat Keju', 'price' => 18000, 'tags' => 'Favorite', 'calories' => 310, 'image_url' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Singkong Goreng Ori', 'price' => 17000, 'tags' => 'Tradisional', 'calories' => 300, 'image_url' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Singkong Goreng Keju', 'price' => 19000, 'tags' => 'Gurih', 'calories' => 350, 'image_url' => 'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Dimsum Mixs', 'price' => 21000, 'tags' => 'Steamed', 'calories' => 280, 'image_url' => 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Martabak Mesir', 'price' => 21000, 'tags' => 'Savory', 'calories' => 390, 'image_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Otak Otak', 'price' => 18000, 'tags' => 'Gurih', 'calories' => 290, 'image_url' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Cireng Sambal Rujak', 'price' => 17000, 'tags' => 'Bestseller', 'calories' => 320, 'image_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Burger',
                    'slug' => 'burger',
                    'type' => 'makanan',
                    'icon' => 'fa-burger',
                ],
                'products' => [
                    ['name' => 'Beef Burger', 'price' => 27000, 'tags' => 'Beef,Bestseller', 'calories' => 520, 'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Burger', 'price' => 25000, 'tags' => 'Crispy', 'calories' => 490, 'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Beef Buritos', 'price' => 28000, 'tags' => 'Wrap,Savory', 'calories' => 460, 'image_url' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Chicken Buritos', 'price' => 27000, 'tags' => 'Wrap', 'calories' => 440, 'image_url' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Hotdog Supreme', 'price' => 33000, 'tags' => 'Supreme', 'calories' => 530, 'image_url' => 'https://images.unsplash.com/photo-1619740455993-9e612b1af08a?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Indomie Series',
                    'slug' => 'indomie-series',
                    'type' => 'makanan',
                    'icon' => 'fa-bowl-rice',
                ],
                'products' => [
                    ['name' => 'Indomie Goreng Ori', 'price' => 17000, 'tags' => 'Classic', 'calories' => 380, 'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Indomie Goreng Telur', 'price' => 19000, 'tags' => 'Favorite', 'calories' => 450, 'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Indomie Goreng Spesial', 'price' => 21000, 'tags' => 'Bestseller', 'calories' => 520, 'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Indomie Rebus Ori', 'price' => 17000, 'tags' => 'Kuah', 'calories' => 370, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Indomie Rebus Telur', 'price' => 19000, 'tags' => 'Comfort Food', 'calories' => 440, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Indomie Rebus Spesial', 'price' => 21000, 'tags' => 'Komplit', 'calories' => 510, 'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Mie Nyemek', 'price' => 25000, 'tags' => 'Bestseller,Pedas', 'calories' => 490, 'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Mie Lada Hitam', 'price' => 25000, 'tags' => 'Spicy', 'calories' => 480, 'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
            [
                'category' => [
                    'name' => 'Salad Series',
                    'slug' => 'salad-series',
                    'type' => 'makanan',
                    'icon' => 'fa-carrot',
                ],
                'products' => [
                    ['name' => 'Chicken Salad', 'price' => 26000, 'tags' => 'Healthy,Fresh', 'calories' => 280, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Maryam Salad', 'price' => 26000, 'tags' => 'Unique', 'calories' => 320, 'image_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'],
                    ['name' => 'Kentang Bolognese', 'price' => 26000, 'tags' => 'Bestseller,Snack', 'calories' => 430, 'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80'],
                ]
            ],
        ];

        $usedSlugs = [];

        foreach ($catalog as $section) {
            $cat = Category::firstOrCreate(
                ['slug' => $section['category']['slug']],
                $section['category']
            );

            foreach ($section['products'] as $prod) {
                $slug = Str::slug($prod['name']);
                if (isset($usedSlugs[$slug])) {
                    $slug = $slug . '-' . $cat->slug;
                }
                $usedSlugs[$slug] = true;

                Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $cat->id,
                        'name' => $prod['name'],
                        'description' => $prod['description'] ?? '',
                        'price' => $prod['price'],
                        'image_url' => $prod['image_url'],
                        'calories' => $prod['calories'] ?? 200,
                        'rating' => 4.8,
                        'tags' => $prod['tags'] ?? 'Popular',
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
