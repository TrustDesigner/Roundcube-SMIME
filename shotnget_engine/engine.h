/**
 * Shotnget SMIME / engine.h
 *
 * @version 1.0
 *
 * shotnget_smime is a roundcube plugin used for SMIME signature / decipherment and connections
 * Copyright (C) 2007-2014 Trust Designer,  Tourte Alexis
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
#ifndef	__SHOTNGET_ENGINE_H__
#define	__SHOTNGET_ENGINE_H__

#include <openssl/engine.h>
#include <openssl/crypto.h>
#include <openssl/buffer.h>
#include <openssl/bn.h>

extern char *filename;

int	shotnget_rsa_sign(int dtype, const unsigned char *m, unsigned int *m_len,
			  unsigned char *sigret, unsigned int *siglen, const RSA *rsa);


#endif
