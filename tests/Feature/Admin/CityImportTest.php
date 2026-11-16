<?php

use App\Enums\RoleEnum;
use App\Exports\CitiesTemplateExport;
use App\Models\City;
use App\Models\User;
use App\Services\CityService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

function csvUpload(string $content, string $name = 'cities.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

test('guests and users without permission cannot import cities', function () {
    $file = csvUpload("city,state\nAustin,TX\n");

    $this->post(route('admin.cities.import'), ['file' => $file])
        ->assertRedirect(route('login'));

    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->post(route('admin.cities.import'), ['file' => csvUpload("city,state\nAustin,TX\n")])
        ->assertForbidden();

    expect(City::query()->count())->toBe(0);
});

test('a csv upload creates cities, upper-casing the state and appending sort order', function () {
    City::factory()->create(['city' => 'Existing', 'state' => 'NY', 'sort_order' => 10]);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => csvUpload("city,state,sort_order,is_active\nAustin,tx,,\nDenver,CO,5,0\n"),
        ])
        ->assertRedirect(route('admin.cities.index'))
        ->assertSessionHasNoErrors();

    $austin = City::query()->where('city', 'Austin')->sole();
    $denver = City::query()->where('city', 'Denver')->sole();

    expect($austin->state)->toBe('TX')
        ->and($austin->sort_order)->toBe(11)
        ->and($austin->is_active)->toBeTrue()
        ->and($denver->sort_order)->toBe(5)
        ->and($denver->is_active)->toBeFalse();
});

test('re-uploading updates existing cities without creating duplicates', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => csvUpload("city,state,sort_order,is_active\nAustin,TX,1,1\n"),
        ]);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => csvUpload("city,state,sort_order,is_active\nAustin,TX,9,no\n"),
        ]);

    $austin = City::query()->where('city', 'Austin')->sole();

    expect(City::query()->count())->toBe(1)
        ->and($austin->sort_order)->toBe(9)
        ->and($austin->is_active)->toBeFalse();
});

test('invalid rows are skipped and reported while valid rows still import', function () {
    $content = "city,state\nAustin,TX\n,CA\nDenver,Colorado\nBoise,ID\n";

    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), ['file' => csvUpload($content)])
        ->assertRedirect(route('admin.cities.index'));

    expect(City::query()->orderBy('city')->pluck('city')->all())->toBe(['Austin', 'Boise']);
});

test('the import service reports counts and row numbers for bad rows', function () {
    $report = app(CityService::class)->importFromUpload(
        csvUpload("city,state,sort_order\nAustin,TX,1\n,CA,2\nDenver,Colorado,3\nBoise,ID,abc\nAustin,TX,4\n"),
    );

    expect($report['created'])->toBe(1)
        ->and($report['updated'])->toBe(1)
        ->and($report['skipped'])->toBe(3)
        ->and(collect($report['errors'])->pluck('row')->all())->toBe([3, 4, 5]);
});

test('an excel file can be imported', function () {
    City::factory()->create(['city' => 'Austin', 'state' => 'TX', 'sort_order' => 99]);

    $xlsx = Excel::raw(new CitiesTemplateExport, ExcelType::XLSX);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => UploadedFile::fake()->createWithContent('cities.xlsx', $xlsx),
        ])
        ->assertRedirect(route('admin.cities.index'));

    expect(City::query()->count())->toBe(2)
        ->and(City::query()->where('city', 'Austin')->sole()->sort_order)->toBe(31)
        ->and(City::query()->where('city', 'Denver')->sole()->sort_order)->toBe(32);
});

test('latitude and longitude columns are optional and saved when present', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => csvUpload("city,state,latitude,longitude\nAustin,TX,30.27,-97.74\nBoise,ID,,\nBad,CA,999,1\n"),
        ])
        ->assertRedirect(route('admin.cities.index'));

    $austin = City::query()->where('city', 'Austin')->sole();

    expect($austin->latitude)->toBe(30.27)
        ->and($austin->longitude)->toBe(-97.74)
        ->and(City::query()->where('city', 'Boise')->sole()->latitude)->toBeNull()
        ->and(City::query()->where('city', 'Bad')->exists())->toBeFalse();
});

test('the template includes latitude and longitude headings', function () {
    expect((new CitiesTemplateExport)->headings())->toBe([
        'city', 'state', 'sort_order', 'is_active', 'latitude', 'longitude',
    ]);
});

test('the import requires a csv or excel file', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [])
        ->assertSessionHasErrors(['file']);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.import'), [
            'file' => UploadedFile::fake()->create('cities.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['file']);
});

test('the template can be downloaded as csv and excel', function () {
    $csv = $this->actingAs($this->admin)->get(route('admin.cities.template', ['format' => 'csv']));
    $csv->assertOk()->assertDownload('cities-template.csv');

    $this->actingAs($this->admin)
        ->get(route('admin.cities.template'))
        ->assertOk()
        ->assertDownload('cities-template.xlsx');
});

test('the template cannot be downloaded without the import permission', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)->get(route('admin.cities.template'))->assertForbidden();
});
