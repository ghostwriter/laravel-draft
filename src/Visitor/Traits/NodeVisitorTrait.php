<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Visitor\Traits;

use PhpParser\Node;
use PhpParser\NodeTraverser;

trait NodeVisitorTrait
{
    /**
     * Called once after traversal.
     *
     * Return value semantics:
     * - null:      $nodes stays as-is
     * - otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     * - null: $nodes stays as-is
     * - otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes of the current node will not be traversed
     * for any visitors.
     *
     * For subsequent visitors NodeVisitor::enterNode() will still be called on the current node and
     * NodeVisitor::leaveNode() will also be invoked for the current node.
     */
    public static function dontTraverseChildren(): int
    {
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  - null => $node stays as-is
     *  - NodeTraverser::dontTraverseChildren() => Children of $node are not traversed. $node stays as-is
     *  - NodeTraverser::stopTraversal() => Traversal is aborted. $node stays as-is
     *  - otherwise => $node is set to the return value
     *
     * @param Node $node Node
     */
    public function enterNode(Node $node): null|int|Node
    {
        return null;
    }

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  - null => $node stays as-is
     *  - NodeTraverser::removeNode() => $node is removed from the parent array
     *  - NodeTraverser::stopTraversal() => Traversal is aborted. $node stays as-is
     *  - array (of Nodes) => The return value is merged into the parent array (at the position of the $node)
     *  - otherwise => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node|Node[] Replacement node (or special return value)
     */
    public function leaveNode(Node $node): mixed
    {
        return null;
    }

    /**
     * If NodeVisitor::leaveNode() returns REMOVE_NODE for a node that occurs in an array, it will be removed from the
     * array.
     *
     * For subsequent visitors leaveNode() will still be invoked for the removed node.
     */
    public static function removeNode(): int
    {
        return NodeTraverser::REMOVE_NODE;
    }

    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns STOP_TRAVERSAL, traversal is aborted.
     *
     * The afterTraverse() method will still be invoked.
     */
    public static function stopTraversal(): int
    {
        return NodeTraverser::STOP_TRAVERSAL;
    }

    public function traverse(array $nodes, ?NodeTraverser $nodeTraverser = null): iterable
    {
        yield from ($nodeTraverser ??= new NodeTraverser())
            ->traverse($nodes);
    }

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes of the current node will not
     * be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well. leaveNode() will be invoked for visitors that has
     * enterNode() method invoked.
     */
    protected static function dontTraverseCurrentAndChildren(): int
    {
        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }
}
