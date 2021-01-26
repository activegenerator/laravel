{{ $marker->start() }}
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $yaml->table }}');
    }
}
{{-- // @@schema:{!! $json !!} --}}
{{ $marker->end() }}
