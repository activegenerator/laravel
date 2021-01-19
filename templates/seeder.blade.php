namespace Database\Seeders;
use App\Models\{{ $yaml->getName('Entity') }};
use Illuminate\Database\Seeder;

class {{ $yaml->getName('Entity') }}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {{ $yaml->getName('Entity') }}::factory()->create();
    }
}
