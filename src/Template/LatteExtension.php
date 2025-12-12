<?php

namespace WebChemistry\Images\Template;

use Clear01\BootstrapForm\Nodes\PairNode;
use Latte\Extension;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use WebChemistry\Images\Template\Nodes\ImgNode;

class LatteExtension extends Extension
{
	public function __construct(private readonly ImageFacade $imageStorageFacade)
	{
	}

	public function getTags(): array
	{
		return [
			'img' => ImgNode::create(...),
		];
	}

	public function getProviders(): array
	{
		return ['imageStorageFacade' => $this->imageStorageFacade];
	}
}
