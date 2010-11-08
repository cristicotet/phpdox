<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox {

   /**
    * Stack Handler for class elements
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   class classStackHandler extends stackHandler {

      public function process(processContext $context, Array $stack) {
         $node = $this->createNode('class', $context->getStackNode());
         $context->addStackNode($node);

         $class = $this->findClass($stack);
         $context->setClass($class[1]);
         $node->setAttribute('name', $context->class);

         if ($extends = $this->findExtends($stack)) {
            $node->setAttribute('extends', $extends);
         }

         if ($implements = $this->findImplements($stack)) {
            foreach($implements as $int) {
               $this->createNode('implements', $node)->setAttribute('interface', $int);
            }
         }
         $modifier = $this->findModifier($stack);
         $node->setAttribute('abstract', $modifier == 'abstract' ? 'true' : 'false');
         $node->setAttribute('final', $modifier == 'final' ? 'true' : 'false');
         $node->setAttribute('line', $class[2]);
      }


      protected function findClass(Array $stack) {
         $pos = $this->findTok(T_CLASS, $stack);
         return $stack[$pos+1];
      }

      protected function findExtends(Array $stack) {
         $pos = $this->findTok(T_EXTENDS, $stack);
         if (is_null($pos)) return;
         $max = count($stack);
         for($t=$pos+1; $t<$max; $t++) {
            if ($stack[$t][0]==T_IMPLEMENTS) break;
            $res[] = $stack[$t][1];
         }
         return join($res);
      }

      protected function findImplements(Array $stack) {
         $pos = $this->findTok(T_IMPLEMENTS, $stack);
         if (is_null($pos)) return;
         $max = count($stack);
         $res = array();
         for($t=$pos+1; $t<$max; $t++) {
            if ($stack[$t]===',') {
               $res[] = ',';
               continue;
            }
            if ($stack[$t][0]==T_EXTENDS) break;
            $res[] = $stack[$t][1];
         }
         return explode(',', join($res));
      }

      protected function findModifier(Array $stack) {
         $pos = $this->findTok(T_FINAL, $stack);
         if ($pos!==null) return 'final';

         $pos = $this->findTok(T_ABSTRACT, $stack);
         if ($pos!==null) return 'abstract';
      }

   }

}