<?php
/*
 * Copyright (C) 2010 Urban Suppiger, Pirmin Mattmann
 *
 * This file is part of eCamp.
 *
 * eCamp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * eCamp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with eCamp.  If not, see <http://www.gnu.org/licenses/>.
 */

	
	class print_build_story_class
	{
		public $data;
		public $y;
		
		function print_build_story_class( $data )
		{
			$this->data = $data;
		}

		function story_h( $pdf, $day )
		{
			return 5 * max( 1, $pdf->getNumLines( $day->story, 190 ) ) + 5;
		}
		
		function build( $pdf )
		{
			$pdf->SetAutoPageBreak(false);

			$pdf->AddPage('P', 'A4');

			$pdf->SetFillColor( 200, 200, 200 );
			$pdf->SetDrawColor( 0, 0, 0 );

			$pdf->SetXY( 10, 10 );
			$pdf->SetFont('','B',16);
			$pdf->Cell( 170, 16, 'Roter Faden', 0, 1, 'L' );

			$pdf->Bookmark( 'Roter Faden', 0, $this->y );

			$this->y = 26;
			
			foreach( $this->data->subcamp as $subcamp )
			{
				foreach( $subcamp->get_sorted_day() as $day )
				{					
					if( $this->story_h( $pdf, $day ) > ( 310 - $this->y ) && $this->y > 150 )
					{	$pdf->addPage('P', 'A4');	$this->y = 15;	}

					$pdf->SetFont( '', 'B', 8 );
					
					$pdf->RoundedRect( 10, $this->y, 190, 4, 2, '1001', 'DF' );
					$pdf->SetXY( 10, $this->y );
					$pdf->drawTextBox( 'Tag '.$day->day_nr, 190, 4, 'L', 'M', 0 );
					
					$this->y += 4;
					
					$pdf->SetFont( '', '', 8 );
					$pdf->SetXY( 10, $this->y );
					$pdf->MultiCell( 190, 4, $day->story, 0, 'L', 0, 1 );
					
					$ey = $pdf->GetY();
					$pdf->RoundedRect( 10, $this->y, 190, $ey - $this->y, 2, '0110', 'D' );
					
					$this->y = $ey + 3;
				}
			}			
		}
	}
	
?>