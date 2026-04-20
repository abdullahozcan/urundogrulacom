<?php
$files = glob("database/migrations/*_create_companies_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/Schema::create\(.*?\{.*?\};/s', "Schema::create('companies', function (Blueprint \$table) {\n            \$table->id();\n            \$table->string('name');\n            \$table->boolean('is_active')->default(true);\n            \$table->timestamps();\n        });", $content);
file_put_contents($files[0], $content);

$files = glob("database/migrations/*_create_products_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/Schema::create\(.*?\{.*?\};/s', "Schema::create('products', function (Blueprint \$table) {\n            \$table->id();\n            \$table->foreignId('company_id')->constrained()->cascadeOnDelete();\n            \$table->string('name');\n            \$table->text('description')->nullable();\n            \$table->string('image')->nullable();\n            \$table->timestamps();\n        });", $content);
file_put_contents($files[0], $content);

$files = glob("database/migrations/*_create_batches_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/Schema::create\(.*?\{.*?\};/s', "Schema::create('batches', function (Blueprint \$table) {\n            \$table->id();\n            \$table->foreignId('product_id')->constrained()->cascadeOnDelete();\n            \$table->string('batch_number');\n            \$table->integer('quantity');\n            \$table->timestamps();\n        });", $content);
file_put_contents($files[0], $content);

$files = glob("database/migrations/*_create_items_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/Schema::create\(.*?\{.*?\};/s', "Schema::create('items', function (Blueprint \$table) {\n            \$table->id();\n            \$table->foreignId('batch_id')->constrained()->cascadeOnDelete();\n            \$table->uuid('uuid')->unique();\n            \$table->boolean('is_scanned')->default(false);\n            \$table->timestamps();\n        });", $content);
file_put_contents($files[0], $content);

$files = glob("database/migrations/*_create_scans_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/Schema::create\(.*?\{.*?\};/s', "Schema::create('scans', function (Blueprint \$table) {\n            \$table->id();\n            \$table->foreignId('item_id')->constrained()->cascadeOnDelete();\n            \$table->string('ip_address')->nullable();\n            \$table->decimal('lat', 10, 8)->nullable();\n            \$table->decimal('lng', 11, 8)->nullable();\n            \$table->string('browser')->nullable();\n            \$table->string('device_type')->nullable();\n            \$table->timestamp('scanned_at')->useCurrent();\n            \$table->timestamps();\n        });", $content);
file_put_contents($files[0], $content);

$files = glob("database/migrations/*_add_company_id_to_users_table.php");
$content = file_get_contents($files[0]);
$content = preg_replace('/public function up\(\): void\s*{\s*Schema::table\(\'users\', function \(Blueprint \$table\) \{\s*//s', "public function up(): void\n    {\n        Schema::table('users', function (Blueprint \$table) {\n            \$table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();\n        ", $content);
file_put_contents($files[0], $content);
