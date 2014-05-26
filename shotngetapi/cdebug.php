<?php

if(isset($init_flag) == false)
	die;
	
/** \file    cdebug.php
    \author  Auditiel
    \author  Jérôme Dusautois
    \date    16/01/2008
    \version 1.0.0
    
    \brief Fichier contenant les classes de gestion des traces
 */

/** \brief Classe de dump d'une variable. Cette classe a été créée par Daniel Jaenecke.
 */
class cdump {

/*
 * dump.class.php
 * a class for generating dumps from all types of variables
 *
 * Copyright (C) 2002 Daniel Jaenecke <jaenecke@gmx.li>
 *
 *     This program is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program; if not, write to the Free Software
 *     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * HOW TO USE
 * ==========
 *
 * You can assign the data you which to dump in two ways:
 *	a) when creating an object, e.g.
 *		$d = new dump ( $my_data )
 *
 *	b) by calling the method assign()
 *		$d = new dump;
 *		$d->assign( $my_data );
 *	
 * The method get_html() will return an HTML-table representing the
 * data which has been assigned before using one of the methods mentioned
 * above. After either a) or b) a command like
 *		echo $d->get_html ();
 * would output the table.
 *
 * CUSTOMIZING
 * ===========
 *
 * The output is formatted using HTML-inline-styles which are defined in the
 * properties style_key, style_value and style_type. By assigning different
 * values output may be changed easily to fit individual needs.
 *
 * REQUIREMENTS
 * ============
 *
 * This class needs PHP 4.0.2 or later to run; by removing the call to the 
 * function get_ressource_type () it can be used on PHP 4.0.0. Just follow 
 * the comment to do the change
 *
 * TODO
 * ====
 *
 * - method for generating raw output
 * - interface for customizing styles
 *
 */
 
/* PROPERTIES */
	/* data for dumping */
	var
		$data			= NULL;
		
	/* styles for HTML-Output */
	var
		$style_key		= NULL,
		$style_value	= NULL,
		$style_type		= NULL
	;

/* METHODS */
	/*
	 * public constructor dump ( [ mixed data ] )
	 * construcor; accepting data for dumping as parameter
	 */
	function cdump ( $data = NULL) {
	
		/* assign data if available */
		if ( !is_null ( $data ) ) {
		
			$this->assign_data ( $data );
			
		}

		/* set up styles for HTML-output */
		$this->style_key 	= 'font-family: sans-serif; font-size: 11px; font-weight: bold; background-color: #f0f0f0;';
		$this->style_value	= 'font-family: monospace; font-size: 11px;';
		$this->style_type 	= 'font-family: sans-serif; font-size: 9px; font-style: italic; color: #000080;';
			
	} /* function dump */
	
	/*
	 * public string get_html ( [ mixed data ] )
	 * creates and returns an HTML-Table from this->data
	 */
	function get_html ( ) {

		if ( !isset ( $this->data ) ) {
		
			return false;
			
		}
		else {
		
			return $this->_make_HTML_table ( $this->data );
			
		}

	} /* function get_html */
	
	/*
	 * public void assign_data ( mixed data )
	 * assign data for later dump
	 */
	function assign_data ( $data ) {
	
		$this->data 	= $data;
	
	} /* function assign_data */
	 
	/*
	 * private string _make_HTML_table ( mixed data ) 
	 * 
	 */
	function _make_HTML_table ( $data ) {
	
		if ( !is_array ( $data ) ) {
		
			switch ( gettype ( $data ) ) {
			
				case 'string':
					
					return ( isset ( $data ) && !empty ( $data ) ) ? 
						htmlentities ( $data ) :
						'&nbsp;'
					;
					break; /* string */
					
				case 'boolean':
					
					return $data ? 'true' : 'false';
					
					break; /* boolean */
					
				case 'object':
				
					$object_data = array (

						'class'			=> get_class ( $data ),
						'parent_class'	=> get_parent_class ( $data ),
						'methods'		=> get_class_methods ( get_class ( $data ) ),
						'properties'	=> get_object_vars ( $data )						
					
					);
				
					return $this->_make_HTML_table ( $object_data );
					
					break; /* object */

				case 'resource':

					/*
					 * use the next line of code when 
					 * using PHP 4.0.0 or PHP 4.0.1
					 */
					// return $data;				

					/* 
					 * use the next line of code
					 * when using PHP 4.0.2 or better 
					 */
					return sprintf ( '%s (%s)', $data, get_resource_type ( $data ) );
					
					break; // resource
					
				default: 
					
					return $data;
					
					break; /* default */
					
			} /* switch gettype */
			
		} /* if !array data */

		$output = '<table border="1" cellpadding="0" cellspacing="0">';

		foreach ( $data as $key => $value ) {
			
			$type = substr ( gettype ( $data[ $key ] ), 0, 3 );
			
			$output .= sprintf ( 
				'<tr>
					<td style="%s">%s</td>
					<td style="%s">%s</td>
					<td style="%s">%s</td>
				</tr>',
				
				$this->style_key, $key,
				$this->style_type, $type,
				$this->style_value, $this->_make_HTML_table ( $value )

			);

		} /* foreach data */

		$output .= '</table>';
		
		return $output;
	
	} /* function _make_HTML_table */

} /* class dump */

/** \brief Classe de trace dans un fichier.
 */
class ctrace
{
  /** \brief Constructeur de l'objet.
   *@param name Nom du fichier à générer.
   */     
  function ctrace( $name )
  {
    $this->Name = $name;
  }
  /** \brief Ecriture d'un fichier complet. Si le fichier existe, il est remplacé.
   *@param data Données à écrire.
   */     
  function writefile($data)
  {
    $trace = fopen( $this->Name, "w" );
    if( $trace != null )
    {
      fwrite( $trace, $data, strlen($data) );
      fclose( $trace);
    }
  }
  /** \brief Ecriture d'une ligne à la fin du fichier de trace.
   *@param data Données de la ligne à écrire.
   */     
  function writeline($data)
  {
    $trace = fopen( $this->Name, "a+");
    if( $trace != null )
    {
      $data .= "\r\n";
      fwrite( $trace, $data, strlen($data) );
      fclose( $trace);
    }
  }
  /** \brief Ecriture du vidage d'une variable au format html.
   *@param var Nom de la variable à vider.
   */     
  function dump($var)
  {
    $cdump = new cdump( $var );
    $this->writefile( $cdump->get_html() );
  }
}

/** \brief Classe de debug d'un utilisateur
 */
class cdebug
{
  const LEVEL0=0; // Pas de trace
  const LEVEL1=1; // Trace entrée sortie de fonction
  const LEVEL2=2; // Trace intermédiares
  const LEVEL3=3; // Trace intermédiaire (dump)
  const LEVEL4=4; // Trace intermédiaire complète 
  /** \brief Constructeur de l'objet.
   *@param id ID de l'utilisateur.
   *@param name Nom de l'utilisateur   
   */     
  function cdebug( $dir, $id=0, $name='', $level=cdebug::LEVEL0 )
  {
    
    $this->ID = $id;
    $this->name = $name;
    $this->dirtrace = $dir;
    $this->funcin = array();
    $this->classin = array();
    $this->traceclass = array();
    $this->deep = 0;
    $this->ctrace = null;
    $this->traceclassdisabled = array();
    $this->setlevel($level);
    
    // Désactive les traces de la classe casn1
    $this->enableclass( "casn1", false );
  }
  
  /** \brief Positionne le niveau de trace courant. si la trace n'était pas encore initialisée, elle l'est.
   */  
  function setlevel( $Level )
  {
    $this->level = is_numeric($Level) ? $Level : 0;
    if( $this->level > cdebug::LEVEL0 && ($this->ctrace == null) )
    {
      if( is_dir($this->dirtrace) )
        $this->ctrace = new ctrace($this->dirtrace.$this->name.".".$this->ID.".log");
      else
       $this->ctrace = null;
    }   
  }

  /** \brief Active ou désactive les traces pour une classe
   * @param classname Nom de la classe
   * @param enable Active les traces si true     
   */  
  function enableclass( $classname, $enable=true)
  {
    if( $enable == true )
      unset($this->traceclassdisabled[$classname]);
    else
      $this->traceclassdisabled[$classname] = true;
  }
  /** \brief Retroune le niveau de trace courant
   */  
  function getlevel()
  {
    return $this->level;
  }

  /** \brief Positionne le niveau de profondeur.
   */  
  function resetdeep( $deep=1 )
  {
    $this->deep = is_numeric($deep) ? $deep : 1;
  }
  
  /** \brief Initialisation d'une nouvelle trace
   */  
  function init()
  {
    // Ecrase le fichier avec la ligne de début
    if( $this->ctrace != null )
      $this->ctrace->writefile( $this->formatline("Initialisation trace\r\n") );    
    
  }
  
  /** \brief Entrée dans une fonction d'une classe
   * @param Fonc Nom dela fonction
   * @param Class Nom de la classe
   */
  function tracein( $Fonc, $Class='', $Info="" )
  {
    $this->classin[$this->deep] = $Class;
    $this->funcin[$this->deep] = $Fonc;
    if( !empty($Class) && !empty($this->traceclassdisabled[$Class]) ) 
      $this->traceclass[$this->deep] = false;
    else
      $this->traceclass[$this->deep] = true;
    $this->deep++;
    
    $msg = "Entree Fonc=$Fonc";    
    if( !empty($Class) )
      $msg .= " Class=$Class";
    if( !empty($Info) )
      $msg .= " $Info";
    $this->write( $msg );
  }           
  
  /** \brief Sortie d'une fonction
   * @param Res résultat  
   */
  function traceout($Res='', $Errno='')
  {
    $msg = '';
    if( $this->deep > 0 )
    {
      $fonc = $this->funcin[$this->deep-1];
      $class = $this->classin[$this->deep-1];
    }
    else
    {
      $fonc = "deep-";
      $class = "";
    }
    $msg .= "Sortie Fonc=".$fonc;
    if( !empty($class) )
      $msg .= " Class=".$class;
    if( !empty($Res) )
    {
      if( !empty($Errno) )
        $msg .= " Erreur=$Errno Resultat=$Res";
      else
        $msg .= " Resultat=$Res";      
    }    
    $this->write( $msg );
    if( $this->deep > 0 )
      $this->deep--;
  } 

  /** \brief Trace dans une fonction
   * @param Msg message  
   */
  function trace($Msg, $Level=cdebug::LEVEL2 )
  {
    if( $this->level >= $Level )
    {
      $this->write( $Msg );
    }
  } 
            
  /** \brief Vide une variable
   * @param Name Nom de la variable
   * @param Var La variable   
   */
  function dump($Name, $Data)
  {
    if( $this->level >= cdebug::LEVEL3 )
    {
      $this->write("Variable $Name" );
  		foreach ( $Data as $key => $value )
      {
  			$type = substr ( gettype ( $data[ $key ] ), 0, 3 );
  			$msg = "  $key=$value";
  			$this->write( $msg );
      }  			
    }
  } 
  
  /** \brief Ecriture d'une ligne
   */  
  function write($line)
  {
    if( $this->ctrace != null ) {
      if( $this->deep > 0 && $this->traceclass[$this->deep-1] == true ) {
			$this->ctrace->writeline($this->formatline($line));
		}
	}
  }

  /** \brief Formatage d'une ligne
   */  
  private function formatline( $Line )
  {
    $mt = explode( " ", microtime() );
    $mt = substr( $mt[0],0,8 );
    $msg = date('Y/m/d H:i:s')." ".$mt." ".memory_get_usage().str_pad(" ", $this->deep ).$Line;
    return $msg;
  }

}
?>
