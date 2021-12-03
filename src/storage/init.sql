create table if not exists "Account" (
    "id"        uuid not null primary key,
    "email"     text not null,
    "pass"      text not null,
    "firstName" text not null,
    "lastName"  text not null,
    "position"  text not null,
    "score"     int  not null
);

create table if not exists "QuestionAnswerInput" (
    "id"          uuid    not null primary key,
    "title"       text    not null,
    "description" text    not null,
    "answers"     text[]  not null,
    "reward"      integer not null
);

create table if not exists "QuestionMultiChoice" (
    "id"       uuid    not null primary key,
    "title"    text    not null,
    "variants" text[]  not null,
    "corrects" text[]  not null,
    "reward"   integer not null
);

create table if not exists "Test" (
    "id"          uuid   not null primary key,
    "title"       text   not null,
    "questionIds" uuid[] not null
);

create table if not exists "Event" (
    "id"          uuid      not null primary key,
    "title"       text      not null,
    "description" text      not null,
    "timestamp"   timestamp not null,
    "accountIds"  uuid[]    not null
);

drop type if exists "lpType";
create type "lpType" as enum ('normal', 'exam');

create table if not exists "LP" (
    "id"               uuid     not null primary key,
    "title"            text     not null,
    "description"      text     not null,
    "linkedAccountIds" uuid[]   not null,
    "testIds"          uuid[]   not null,
    "eventIds"         uuid[]   not null,
    "type"             "lpType" not null,
    "price"            integer  not null,
    "x"                integer,
    "y"                integer
);

create table if not exists "Arch" (
    "id"          uuid   not null primary key,
    "title"       text   not null,
    "description" text   not null,
    "lpIds"       uuid[] not null
);

create table if not exists "ArchNode" (
    "id"         uuid   not null primary key,
    "archId"     uuid   not null references "Arch" ("id"),
    "nextArchId" uuid[] not null
);

create table if not exists "OnboardingRoute" (
    "id"          uuid   not null primary key,
    "accountId"   uuid   not null references "Account" ("id"),
    "archIds"     uuid[] not null,
    "startArchId" uuid   not null references "Arch" ("id")
);
