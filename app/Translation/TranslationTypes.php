<?php

namespace App\Translation;

use App\Models\Article;
use App\Models\BadgeDefinition;
use App\Models\CourseModule;
use App\Models\Exam;
use App\Models\Lab;
use App\Models\LegalPage;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * What can be translated: each content type, its model and its translatable fields with the
 * rule used to find prose in them (see Segments). Staff-only fields (instructor notes) and
 * technical fields are left out on purpose.
 */
final class TranslationTypes
{
    public const LOCALES = ['fr'];

    private const TYPES = [
        'programme' => ['model' => Specialization::class, 'fields' => [
            'title' => 'text', 'summary' => 'text', 'description' => 'markdown',
            'audience' => 'list', 'prerequisites' => 'list', 'outcomes' => 'list', 'inclusions' => 'list', 'faqs' => 'faqs',
        ]],
        'module' => ['model' => CourseModule::class, 'fields' => [
            'title' => 'text', 'purpose' => 'text', 'prerequisite_knowledge' => 'text',
            'objectives' => 'list', 'key_concepts' => 'list', 'assessment_criteria' => 'list',
        ]],
        'lesson' => ['model' => Lesson::class, 'fields' => [
            'title' => 'text', 'objectives' => 'list', 'body_md' => 'markdown', 'knowledge_checks' => 'checks',
        ]],
        'lab' => ['model' => Lab::class, 'fields' => [
            'title' => 'text', 'summary' => 'text', 'content' => 'lab_content', 'validator' => 'lab_validator',
        ]],
        // Options are separate rows; their text is addressed as "options.<position>".
        'question' => ['model' => Question::class, 'fields' => [
            'prompt' => 'text', 'explanation' => 'text', 'objective' => 'text',
        ]],
        'exam' => ['model' => Exam::class, 'fields' => ['title' => 'text']],
        'badge' => ['model' => BadgeDefinition::class, 'fields' => ['name' => 'text', 'description' => 'text', 'criteria' => 'text']],
        'article' => ['model' => Article::class, 'fields' => ['title' => 'text', 'summary' => 'text', 'body_md' => 'markdown']],
        'legal' => ['model' => LegalPage::class, 'fields' => ['title' => 'text', 'summary' => 'text', 'body_md' => 'markdown']],
    ];

    public static function all(): array
    {
        return array_keys(self::TYPES);
    }

    public static function exists(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function fields(string $type): array
    {
        return self::TYPES[$type]['fields'] ?? throw new InvalidArgumentException("Unknown translation type {$type}");
    }

    /** @return class-string<Model> */
    public static function model(string $type): string
    {
        return self::TYPES[$type]['model'] ?? throw new InvalidArgumentException("Unknown translation type {$type}");
    }

    public static function typeOf(Model $model): string
    {
        foreach (self::TYPES as $type => $def) {
            if ($model instanceof $def['model']) {
                return $type;
            }
        }
        throw new InvalidArgumentException('Not a translatable model: '.$model::class);
    }

    /** Rule for a segment path, from its root field ("options.N" belongs to questions). */
    public static function ruleFor(string $type, string $path): string
    {
        $root = preg_split('/[.\[]/', $path, 2)[0];

        return $type === 'question' && $root === 'options' ? 'text' : (self::fields($type)[$root] ?? 'text');
    }
}
