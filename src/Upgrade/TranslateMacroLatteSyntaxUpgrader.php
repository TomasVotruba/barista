<?php

declare(strict_types=1);

namespace Barista\Upgrade;

use Barista\Contract\LatteSyntaxUpgraderInterface;
use Nette\Utils\Strings;

final class TranslateMacroLatteSyntaxUpgrader implements LatteSyntaxUpgraderInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/oIrbud/1
     */
    private const UNDERSCORE_REGEX = '#\{_\}(?<content>.*?)\{\/_\}#m';

    public function upgrade(string $fileContent): string
    {
        return Strings::replace(
            $fileContent,
            self::UNDERSCORE_REGEX,
            function (array $match): string {
                return sprintf('{translate}%s{/translate}', $match['content']);
            }
        );
    }
}
