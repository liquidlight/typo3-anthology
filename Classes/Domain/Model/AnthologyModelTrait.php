<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Domain\Model;

use DateTime;
use DateTimeInterface;
use LiquidLight\Anthology\Domain\Model\Category;
use LiquidLight\Anthology\Domain\Model\Content;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

trait AnthologyModelTrait
{
	protected ?string $title = null;

	protected ?string $slug = null;

	protected ?DateTimeInterface $crdate = null;

	protected ?DateTimeInterface $tstamp = null;

	protected ?string $abstract = null;

	protected ?FileReference $image = null;

	protected ?string $bodytext = null;

	/**
	 * @var ObjectStorage<Content>
	 */
	protected ?ObjectStorage $content = null;

	protected ?string $relatedPages = null;

	/**
	 * @var ObjectStorage<FileReference>
	 */
	protected ?ObjectStorage $files = null;

	protected ?string $link = null;

	protected ?string $seoTitle = null;

	protected ?string $seoDescription = null;

	/**
	 * @var ObjectStorage<Category>
	 */
	protected ?ObjectStorage $categories = null;

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): void
	{
		$this->slug = $slug;
	}

	public function getCrdate(): DateTime
	{
		return $this->crdate;
	}

	public function setCrdate(DateTime $crdate): void
	{
		$this->crdate = $crdate;
	}

	public function getTstamp(): DateTime
	{
		return $this->tStamp;
	}

	public function setTstamp(DateTime $tStamp): void
	{
		$this->tStamp = $tStamp;
	}

	public function getAbstract(): ?string
	{
		return $this->abstract;
	}

	public function setAbstract(string $abstract): void
	{
		$this->abstract = $abstract;
	}

	public function getImage(): ?FileReference
	{
		return $this->image;
	}

	public function setImage(?FileReference $image): void
	{
		$this->image = $image;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function setLink(?string $link): void
	{
		$this->link = $link;
	}

	public function getBodytext(): ?string
	{
		return $this->bodytext;
	}

	public function setBodytext(string $bodytext): void
	{
		$this->bodytext = $bodytext;
	}

	public function getContent(): ?ObjectStorage
	{
		// @extensionScannerIgnoreLine
		return $this->content;
	}

	public function setContent(ObjectStorage $content): void
	{
		// @extensionScannerIgnoreLine
		$this->content = $content;
	}

	public function getRelatedPages(): array
	{
		if (!$this->relatedPages || $this->relatedPages === '') {
			return [];
		}

		return GeneralUtility::intExplode(',', $this->relatedPages, true);
	}

	public function setRelatedPages(string $relatedPages): void
	{
		$this->relatedPages = $relatedPages;
	}

	public function getFiles(): ObjectStorage
	{
		return $this->files;
	}

	public function setFiles(ObjectStorage $files): void
	{
		$this->files = $files;
	}

	public function getSeoTitle(): ?string
	{
		return $this->seoTitle;
	}

	public function setSeoTitle(string $seoTitle): void
	{
		$this->seoTitle = $seoTitle;
	}

	public function getSeoDescription(): ?string
	{
		return $this->seoDescription;
	}

	public function setSeoDescription(string $seoDescription): void
	{
		$this->seoDescription = $seoDescription;
	}

	public function getCategories(): ObjectStorage
	{
		return $this->categories;
	}

	public function setCategories(ObjectStorage $categories): void
	{
		$this->categories = $categories;
	}
}
