<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Generator;
use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

final class FindControllers
{
    /**
     * @var array<Node>
     */
    private array $controllers = [];
    /**
     * @throws FileNotFoundException
     *
     * @return Generator<string,array<Stmt>>
     */
    public function __invoke(Draft $draft, Filesystem $filesystem): Generator
    {
        $traverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        $nodeFinder = new NodeFinder();
        foreach ($filesystem->files($draft->controllerPath()) as $controller) {
            $path = $controller->getRealPath();
            if (false === $path) {
                throw new FileNotFoundException();
            }

            $nodes = $draft->parse($filesystem->get($path), $controller->getFilename());

            /** @var array<Class_> $classes */
            $classes = $nodeFinder->findInstanceOf($nodes, Class_::class);
            foreach ($classes as $class){
                $className = NodeExtractor::getName($class);

                /** @var array<ClassConst> $constants */
                $constants = $nodeFinder->findInstanceOf($class, ClassConst::class);
                foreach ($constants as $constant){
                    $constName = NodeExtractor::getName($constant);
                    $constValue = NodeExtractor::getValue($constant);

                    $this->controllers[$path][$className]['constant'][$constName] = $constValue;
                }

                /** @var array<ClassMethod> $methods */
                $methods = $nodeFinder->findInstanceOf($class, ClassMethod::class);
                foreach ($methods as $method){
                    $methodName = NodeExtractor::getName($method);
                    $methodParams = NodeExtractor::getParams($method);
                    $this->controllers[$path][$className]['methods'][$methodName] = $methodParams;
                }


//                $this->controllers[$path][$className][] = true;


            }
//            yield $path => $draft->parse($filesystem->get($path), $controller->getFilename());
//            $this->controllers[$path] = $ast;
//             [$className]['constant'][sprintf('%s::%s', $className, $constName)]
//                 = $constValue;
        }

//        dd($this);
        yield from $this->controllers;
    }

    /**
     * @return array<string>
     */
    public function getControllers(): array
    {
        return $this->controllers;
    }
}
