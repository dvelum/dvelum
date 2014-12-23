<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2012  Kirill A Egorov
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Page navigator
 */
class Paginator
{
	/**
	 * Current page number
	 * @var integer
	 */
	public $curPage;
	/**
	 * Pages count
	 * @var integer
	 */
	public $numPages;
	/**
	 * Number of buttons
	 * @var integer
	 */
	public $numLinks;
	/**
	 * URL template
	 * @var string
	 */
	public $pageLinkTpl;
	/**
	 * String for replacing page number
	 * @var string
	 */
	public $tplId = '[page]';

	public function __toString()
	{
		if($this->numPages <= 1)
			return '';

		$digits = $this->findNearbyPages();

		$s = '<div class="pager" align="center">';
		$s.= $this->createNumBtns($digits);
		$s.= '</div>';

		return $s;
	}

	public function findNearbyPages()
	{
		$digits = array();

		if($this->numLinks >= $this->numPages)
		{
			for($i = 1; $i <= $this->numPages; $i++)
				$digits[] = $i;

				return $digits;
		}

		if($this->curPage < $this->numLinks)
		{
			for($i = 1; $i <= $this->numLinks; $i++)
				$digits[] = $i;

				return $digits;
		}

		if($this->curPage > $this->numPages - $this->numLinks)
		{
		for($i = $this->numPages - $this->numLinks + 1; $i <= $this->numPages; $i++)
			$digits[] = $i;

			return $digits;
		}

		for($i = $this->curPage - intval($this->numLinks / 2), $j = 0; $j < $this->numLinks; $i++, $j++)
			$digits[] = $i;

			return $digits;
	}

	public function createNumBtns($digits)
	{
	$s = '';

	if($this->curPage > 1)
		$s .= '<a href="' . str_replace($this->tplId , ($this->curPage - 1) , $this->pageLinkTpl) . '"><div class="pager_item">&laquo;</div></a>';


		for($i = 0, $sz = sizeof($digits); $i < $sz; $i++)
			{
			if($digits[$i] == $this->curPage)
		$s .= '<div class="pager_item_selected">' . $digits[$i] . '</div>';
			else
				$s .= '<a href="' . str_replace($this->tplId , $digits[$i] , $this->pageLinkTpl) . '"><div class="pager_item">' . $digits[$i] . '</div></a>';
		}

			if($this->curPage < $this->numPages)
				$s .= '<a href="' . str_replace($this->tplId , ($this->curPage + 1) , $this->pageLinkTpl) . '"><div class="pager_item">&raquo;</div></a>';

		return $s;
	}
}