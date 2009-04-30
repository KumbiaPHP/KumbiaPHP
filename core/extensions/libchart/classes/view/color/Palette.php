<?php
	/* Libchart - PHP chart library
	 * Copyright (C) 2005-2008 Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
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
	 * 
	 */
	
	/**
	 * Color palette shared by all chart types.
	 *
	 * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
	 * Created on 25 july 2007
	 */
	class Palette {
		public $red;
		public $axisColor;
		public $aquaColor;
		
		public $barColorSet;
		public $lineColorSet;
		public $pieColorSet;
	
		/**
		 * Palette constructor.
		 */
		public function Palette() {
			$this->red = new Color(255, 0, 0);
		
			// Colors for the horizontal and vertical axis
			$this->axisColor = array(
					new Color(201, 201, 201),
					new Color(158, 158, 158)
			);

			// Colors for the background
			$this->aquaColor = array(
					new Color(242, 242, 242),
					new Color(231, 231, 231),
					new Color(239, 239, 239),
					new Color(253, 253, 253)
			);
			
			// Colors for the bars
			$this->barColorSet = new ColorSet(array(
					new Color(42, 71, 181),
					new Color(243, 198, 118),
					new Color(128, 63, 35),
					new Color(195, 45, 28),
					new Color(224, 198, 165),
					new Color(239, 238, 218),
					new Color(40, 72, 59),
					new Color(71, 112, 132),
					new Color(167, 192, 199),
					new Color(218, 233, 202)
			), 0.75);

			// Colors for the lines
			$this->lineColorSet = new ColorSet(array(
					new Color(172, 172, 210),
					new Color(2, 78, 0),
					new Color(148, 170, 36),
					new Color(233, 191, 49),
					new Color(240, 127, 41),
					new Color(243, 63, 34),
					new Color(190, 71, 47),
					new Color(135, 81, 60),
					new Color(128, 78, 162),
					new Color(121, 75, 255),
					new Color(142, 165, 250),
					new Color(162, 254, 239),
					new Color(137, 240, 166),
					new Color(104, 221, 71),
					new Color(98, 174, 35),
					new Color(93, 129, 1)
			), 0.75);

			// Colors for the pie
			$this->pieColorSet = new ColorSet(array(
				new Color(2, 78, 0),
				new Color(148, 170, 36),
				new Color(233, 191, 49),
				new Color(240, 127, 41),
				new Color(243, 63, 34),
				new Color(190, 71, 47),
				new Color(135, 81, 60),
				new Color(128, 78, 162),
				new Color(121, 75, 255),
				new Color(142, 165, 250),
				new Color(162, 254, 239),
				new Color(137, 240, 166),
				new Color(104, 221, 71),
				new Color(98, 174, 35),
				new Color(93, 129, 1)
			), 0.7);
		}
	}
?>