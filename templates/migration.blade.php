{{ $marker->start() }}
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
{!! $model->getCode('migration.imports', 0) !!}

class Create{{ $model->str('table', 'studly') }}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{ $model->table }}', function (Blueprint $table) {
@foreach($model->fields as $fields)
    @if($fields->migration )
            {!! $fields->migration !!}
    @endif
@endforeach
{!! $model->getCode('migration.schema_up', 12) !!}
        });
{!! $model->getCode('migration.up', 8) !!}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $model->table }}');
{!! $model->getCode('migration.up', 8) !!}
    }
}
{{-- // @@schema:{!! $json !!} --}}
{{ $marker->end() }}
