# Color variables
C_OK		= tput setaf 2
C_WARNING	= tput setaf 3
C_ERROR		= tput setaf 1
C_RESET		= tput setaf 7

print_ok	= $(C_OK); echo $(1); $(C_RESET)
print_warning	= $(C_WARNING); echo $(1); $(C_RESET)
print_error	= $(C_ERROR); echo $(1); $(C_RESET)

# Start Makefile

NAME		= shotnget_sign_engine.so

CC		= cc

LIB		= 

CFLAGS		= -g -W -ansi -std=gnu99

LDFLAGS		= -shared

SRC		= shotnget_engine.c	\
		  engine.c

INCLUDES	= -Iinclude

OBJ		= $(SRC:.c=.o)

%.o:	%.c
	$(CC) -o $@ -c -fPIC $<  $(CFLAGS) $(INCLUDES)
	@$(call print_ok, [compile $<])

$(NAME):$(OBJ)
	@$(CC) -shared -o $(NAME) $(OBJ) $(LIB)
	@$(call print_ok, compile succesful \($(NAME)\))

all:	$(NAME)
	cp $(NAME) ..


re:	fclean all

clean:
	@rm -rf $(OBJ)

fclean:	clean
	@rm -rf $(NAME)