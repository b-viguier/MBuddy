<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\Component\Trait;
use Bveing\MBuddy\Ui\SubTemplate;
use Bveing\MBuddy\Ui\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testEmpty(): void
    {
        $template = Template::createEmpty();

        self::assertSame('', $template->pattern());
        self::assertEmpty($template->components());
    }

    public function testScalarReplacements(): void
    {
        $template = Template::create(
            '>{{ string }} {{ int }} {{ float }} {{ null }}<',
            string: 'A',
            int: 1,
            float: 1.1,
            null: null,
        );

        self::assertSame('>A 1 1.1 <', $template->pattern());
        self::assertEmpty($template->components());
    }

    public function testIntegerPlaceHolders(): void
    {
        $template = Template::create(
            '>{{ 0 }} {{ 1 }}<',
            'A',
            'B',
        );

        self::assertSame('>A B<', $template->pattern());
        self::assertEmpty($template->components());
    }

    public function testComponentsReplacements(): void
    {
        $components = [
            $this->createComponent(),
            $this->createComponent(),
        ];

        $template = Template::create(
            $pattern = '>{{ 0 }} {{ 1 }}<',
            ...$components,
        );

        self::assertSame($pattern, $template->pattern());
        self::assertSame($components, $template->components());
    }

    public function testReplace(): void
    {
        $result = Template::replace(
            '>{{ 0 }} {{ 1 }} {{ A }} {{ B }}<',
            [
                0 => '00',
                1 => '11',
                'A' => 'AA',
                'B' => 'BB',
            ]
        );

        self::assertSame(
            '>00 11 AA BB<',
            $result,
        );
    }

    public function testSubTemplateSimplification(): void
    {
        $components = [
            $this->createComponent(),
            $this->createComponent(),
        ];
        $subSubTemplate = SubTemplate::create(
            '<subsub>{{ A }}</subsub>',
            A: $components[0],
        );
        $subTemplate = SubTemplate::create(
            '<sub>{{ B }} - {{ C }}</sub>',
            B: $components[1],
            C: $subSubTemplate,
        );
        $template = Template::create('<main>{{ sub }}</main>', sub: $subTemplate);

        self::assertSame(
            '<main><sub>{{ sub#B }} - <subsub>{{ sub#C#A }}</subsub></sub></main>',
            $template->pattern(),
        );
        self::assertSame(
            ["sub#B" => $components[1], "sub#C#A" => $components[0]],
            $template->components(),
        );
    }

    public function testKeysMismatch(): void
    {
        $template = Template::create(
            '>{{ A }} {{ B }}<',
            B: 'B',
            C: 'C',
        );

        self::assertSame('>{{ A }} B<', $template->pattern());
        self::assertEmpty($template->components());
    }

    private function createComponent(): Component
    {
        return new class () implements Component {
            use Trait\AutoId;
            use Trait\AutoVersion;
            public function template(): Template
            {
                return Template::create('<Comp/>');
            }
        };
    }
}
