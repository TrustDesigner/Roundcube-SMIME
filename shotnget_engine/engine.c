/**
 * Shotnget SMIME / engine.c
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
 
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <fcntl.h>

#include <openssl/engine.h>
#include <openssl/pem.h>

#include "engine.h"

static char    *get_file_contents(const char *filename) {
  long  size;
  int   fd = -1;
  char  *contents = NULL;

  if (filename == NULL)
    return NULL;
  if ((fd = open(filename, O_RDONLY)) == -1)
    return NULL;
  if ((size = lseek(fd, 0, SEEK_END) + 1) == -1)
    return NULL;
  lseek(fd, 0, SEEK_SET);
  if ((contents = malloc(size * sizeof(char))) == NULL)
    return NULL;
  if (read(fd, contents, size - 1) == -1)
    return NULL;
  close(fd);
  return contents;
}

static X509    *get_certificate(char *cert) {
  BIO  *bio = NULL;
  X509  *ret = NULL;

  if (cert == NULL)
    return NULL;
  if ((bio = BIO_new_mem_buf(cert, strlen(cert))) == NULL)
    return NULL;
  ret = PEM_read_bio_X509(bio, NULL, 0, NULL);
  BIO_free(bio);
  return ret;
}

static EVP_PKEY        *get_public_key(char *cert_content) {
  EVP_PKEY      *ret = NULL;
  X509		*cert = get_certificate(cert_content);

  ret =  X509_get_pubkey(cert);
  X509_free(cert);
  return ret;
}

EVP_PKEY *shotnget_load_privkey(ENGINE *eng, const char *key_id,
				 UI_METHOD *ui_method, void *callback_data) {
  EVP_PKEY *ret = NULL;

  filename = strdup(key_id);
  ret = get_public_key(get_file_contents(key_id));
  if (ret != NULL) {
    
  }
  return ret;
}
