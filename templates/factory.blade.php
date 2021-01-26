namespace Database\Factories;
use App\Models\{{ $yaml->getName('Entity') }};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class {{ $yaml->getName('Entity') }}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = {{ $yaml->getName('Entity') }}::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
@foreach($yaml->fields as $field)
    @if($field->type->database != "id" && $field->type->database != "foreignId" && $field->slug != "deleted_at")
        @if($field->type->fakerMethod)
          '{{ $field->slug }}' => $this->faker->{{ $field->type->fakerMethod }},
        @elseif($field->type->example)
          '{{ $field->slug }}' => {!!$field->type->example!!},
        @endif
    @endif
    @if($field->type->database == "foreignId")
          '{{ $field->slug }}' => {{ $field->type->relatedRelation()->relatedFull }}::factory(),
    @endif
@endforeach
        ];
    }
}
