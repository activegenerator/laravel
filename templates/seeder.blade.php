namespace Database\Seeders;
use App\Models\{{ $model->getName('Entity') }};
use Illuminate\Database\Seeder;

class {{ $model->getName('Entity') }}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {{ $model->getName('Entity') }}::factory()->create();
    }
}
