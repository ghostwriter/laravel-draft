<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Command;

use Ghostwriter\Draft\Draft;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\PrettyPrinter\Standard;

final class InitCommand extends Command
{
    protected $description = 'Creates a draft.php file in the project base path.';

    protected $signature = 'draft:init';

    public function handle(Filesystem $filesystem): int
    {
        $draftFile = base_path('draft.php');
        if ($filesystem->missing($draftFile)) {
            $filesystem->put(
                $draftFile,
                (new Standard())->prettyPrintFile([
                    new Declare_([new DeclareDeclare(new Identifier('strict_types'), new LNumber(1))]),
                    new Nop(),
                    new Use_([new UseUse(new Name(Draft::class))]),
                    new Nop(),
                    new Return_(new Closure([
                        'attrGroups' => [],
                        'static' => true,
                        'byRef' => false,
                        'params' => [new Param(new Variable('draft'), null, new Name('Draft'))],
                        'uses' => [],
                        'returnType' => new Identifier('void'),
                        'stmts' => [new Nop()],
                    ])),
                ])
            );
            $this->info(sprintf('Created draft file [%s]!', $draftFile));

            return self::SUCCESS;
        }
        $this->error(sprintf('Draft file [%s] already exists!', $draftFile));

        return self::FAILURE;
    }
}
