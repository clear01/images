<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace WebChemistry\Images\Template\Nodes;

use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\Token;

class ImgNode extends StatementNode
{
	public ExpressionNode $name;
	public ExpressionNode $aliases;

	public static function create(Tag $tag): static
	{
		$node = $tag->node = new static;
		$tag->expectArguments();
		$node->name = $tag->parser->parseUnquotedStringOrExpression();

		$aliasesItems = [];
		$stream = $tag->parser->stream;

		// Pokud následuje čárka, parsujeme aliasy
		if ($stream->tryConsume(',')) {
			do {
				// 1. Očekáváme název aliasu (identifikátor)
				$token = $stream->consume(Token::Php_Identifier);
				$aliasName = new StringNode($token->text);

				$args = [];
				// 2. Pokud následuje závorka, parsujeme argumenty aliasu
				if ($stream->tryConsume('(')) {
					while (!$stream->is(')')) {
						$args[] = new ArrayItemNode($tag->parser->parseExpression());
						$stream->tryConsume(',');
					}
					$stream->consume(')');
				}

				// Přidáme do pole: 'nazevAliasu' => [argumenty]
				$aliasesItems[] = new ArrayItemNode(
					new ArrayNode($args),
					$aliasName
				);

			} while ($stream->tryConsume(','));
		}

		$node->aliases = new ArrayNode($aliasesItems);

		return $node;
	}

	public function print(PrintContext $context): string
	{

		return $context->format(
			'$__res = $this->global->imageStorageFacade->create(%node, %node);' .
			'echo $this->global->imageStorageFacade->link($__res);',
			$this->name,
			$this->aliases,
		);
	}

	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->aliases;
	}

}