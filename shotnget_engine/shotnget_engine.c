/**
 * Shotnget SMIME / shotnget_engine.c
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
#include <stdio.h>
#include <stdbool.h>
#include <string.h>
#include <unistd.h>

#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>


#include <openssl/crypto.h>
#include <openssl/objects.h>
#include <openssl/engine.h>
#include <openssl/pem.h>
#include <openssl/x509v3.h>
#include <openssl/rsa.h>
#include <openssl/bn.h>

#include "engine.h"

static const char	*engine_shotnget_id = "shotnget_sign";
static const char	*engine_shotnget_name = "ShotNGet ENGINE";

static const bool	do_dump = true;
static const bool	do_dump_cmd = true;

char			*filename = NULL;
static  int		full_size = 0;

static void dump_cmd(char *type, int tag, const unsigned char *cmd, int size, int step, bool is_last) {
  if (do_dump == false)
    return;
  for (int i = 0; i < step; ++i) {
    fprintf(stderr, "│   ");
  }
  if (size == 0) {
    fprintf(stderr, "%s── Found %s", is_last == true ? "└" : "├", type);
  } else {
    fprintf(stderr, "%s── Found %s (%d) of size : %d %s ", is_last == true ? "└" : "├", type, tag, size, do_dump_cmd == true ? "->" : "");
    if (do_dump_cmd == true) {
      for (int j = 0; j < size; ++j) {
	fprintf(stderr, "%02x ", cmd[j]);
      }
    }
  }
  fprintf(stderr, "\n");
}

static void decode(const unsigned char *input, unsigned char *output, int size, int *cpy_pos, int step, bool is_last) {
  for (int i = 0; i < size; ) {
    int	tag = input[i++];
    int cmd_size = input[i++];
    if (cmd_size > 127) {
      cmd_size = input[i++] + input[i++];
    }
    unsigned char cmd[cmd_size + 1];
    memcpy(cmd, input + i, cmd_size);
    i = i + cmd_size;
    if (tag == 48) {
      dump_cmd("sequence", tag, cmd, cmd_size, step,  false);
      decode(cmd, output, cmd_size, cpy_pos, step + 1, i == size ? true : false);
    } else if (tag == 4) {
      dump_cmd("octet string", tag, cmd, cmd_size, step, i == size ? true : false);
      memcpy(output + *cpy_pos, ":", 1);
      memcpy(output + *cpy_pos + 1, cmd, cmd_size);
      *cpy_pos = *cpy_pos + cmd_size + 1;
    } else if (tag == 6) {
      dump_cmd("object identifier", tag, cmd, cmd_size, step, i == size ? true : false);
      if (cmd_size == 5 && cmd[0] == 43 && cmd[1] == 14 && cmd[2] == 3 && cmd[3] == 2 && cmd[4] == 26) {
	memcpy(output + *cpy_pos, ":SHA1", 5);
	*cpy_pos = *cpy_pos + 5;
      } else {
	memcpy(output + *cpy_pos, ":SHA256", 7);
	*cpy_pos = *cpy_pos + 7;
      }
    } else if (tag == 5) {
      dump_cmd("null object", tag, cmd, cmd_size, step, i == size ? true : false);
    } else {
      dump_cmd("unknown tag", tag, cmd, cmd_size, step, i == size ? true : false);
    }
  }
  dump_cmd(full_size == size ? "end of command" : "end of tag", -1, "", 0, step - 1, is_last);
}

static int	pkey_rsa_encrypt(int flen, const unsigned char *from,
				 unsigned char *to, RSA *rsa, int padding) {
  int			key_size = RSA_size(rsa);
  const unsigned char	*protocol = "shotnget";
  unsigned char		to_buff[1024];
  char			tmp[1024];
  int			copy_pos = 0;
  int			len;

  bzero(to, key_size);
  bzero(to_buff, 1024);
  bzero(tmp, 1024);
  full_size = flen;
  len = sprintf(to, "%s:%d", protocol, key_size);
  dump_cmd("command", -1, from, flen, 0, false);
  decode(from, to_buff, flen, &copy_pos, 1, true);

  sprintf(tmp, ":%d", copy_pos - (strncmp(to_buff, ":SHA1", 5) == 0 ? 6 : 8));

  memcpy(to + len, tmp, strlen(tmp));
  memcpy(to + len + strlen(tmp), to_buff, copy_pos);

  return key_size;
}

static int	pkey_rsa_decrypt(int flen, const unsigned char *from,
                                 unsigned char *to, RSA *rsa, int padding) {
  int ret = 0;
  if (filename != NULL) {
    int fd = open(filename, O_RDONLY);
    char buff[16];

    read(fd, buff, 16);

    if (strncmp(buff, "-----BEGIN CERTIFICATE-----", 16) == 0) {
      write(1, from, flen);
    } else {
      memcpy(to, buff, 16);
      ret = 16;
    }
    close(fd);
  }
  return ret;
}

EVP_PKEY *shotnget_load_privkey(ENGINE *eng, const char *key_id,
				UI_METHOD *ui_method, void *callback_data);

static RSA_METHOD	*shotnget_get_rsa_method() {
  static RSA_METHOD ops;

  if (!ops.rsa_priv_enc) {
    ops = *RSA_get_default_method();
    ops.rsa_priv_enc = pkey_rsa_encrypt;
    ops.rsa_priv_dec = pkey_rsa_decrypt;
  }
  return &ops;
}

/*
** Define engine and initialize it with functions to call when signing
 */

static int	bind_shotnget(ENGINE *e)
{
  if (!ENGINE_set_id(e, engine_shotnget_id)
      || !ENGINE_set_name(e, engine_shotnget_name)
      || !ENGINE_set_RSA(e, shotnget_get_rsa_method())
      || !ENGINE_set_load_privkey_function(e, shotnget_load_privkey)
      )
    return 0;
  return 1;
}

static int bind_fn(ENGINE * e, const char *id)
{
  if (!bind_shotnget(e)) {
    return 0;
  } else {
    return 1;
  }
}

IMPLEMENT_DYNAMIC_CHECK_FN();
IMPLEMENT_DYNAMIC_BIND_FN(bind_fn);
