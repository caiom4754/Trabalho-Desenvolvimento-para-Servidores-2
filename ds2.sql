#criando bd
create database compras;

use compras ;

create table usuarios(
	id_usuario integer not null auto_increment primary  key,
    usuario varchar(15) not null,
    senha varchar(32) not null,
    dtcria datetime default now(),
    estatus char(01) default ''
);

alter table usuarios add column nome varchar(30) default '' after senha,
					 add column tipo varchar(20) default '' after estatus;

alter table usuarios drop column id_usuario;	
alter table usuarios modify usuario varchar(15) not null primary key;

create table unid_medida(
	cod_unidade integer auto_increment primary key,
    sigla varchar(03) default '',
    descricao varchar(30) default '',
    dtcria datetime default now(),
    usucria varchar(15) default '', 
    estatus char(01) default '',
    
    constraint foreign key fk_unidmed_prod (usucria) references usuarios(usuario)
);

create table produtos (
	cod_produto integer auto_increment primary key,
    descricao varchar(30) default '',
    unid_medida integer default 0,
    estoq_minimo integer default 0,
    estoq_maximo integer default 0,
    dtcria datetime default now(),
    usucria varchar(15) default '',
    estatus char(01) default '',
    
    constraint foreign key  fk_prod_unidmed (unid_medida) references unid_medida(cod_unidade),
    constraint foreign key fk_prod_usuarios (usucria) references usuarios(usuario)
);

/*corrigindo erro da fk usucria, passando ela para not null e sem um valor default*/
alter table unid_medida
alter column usucria drop default;

alter table unid_medida
modify column usucria varchar(15) not null;

alter table produtos
alter column usucria drop default;

alter table produtos
modify column usucria varchar(15) not null;

alter table produtos
drop foreign KEY fk_prod_unidmed;

alter table produtos
add constraint fk_prod_unidmed
foreign key(unid_medida) references unid_medida(sigla);

select * from produtos;
