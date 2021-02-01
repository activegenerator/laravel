{{ $marker->start() }}
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
{!! $yaml->getCode('migration.imports', 0) !!}

class Create{{ $yaml->str('table', 'studly') }}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{ $yaml->table }}', function (Blueprint $table) {
@foreach($yaml->fields as $fields)
    @if($fields->migration )
            {!! $fields->migration !!}
    @endif
@endforeach
{!! $yaml->getCode('migration.schema_up', 12) !!}
        });
{!! $yaml->getCode('migration.up', 8) !!}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $yaml->table }}');
{!! $yaml->getCode('migration.up', 8) !!}
    }
}
{{-- // @@schema:{!! $json !!} --}}
{{ $marker->end() }}
