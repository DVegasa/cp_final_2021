<?php

namespace dvegasa\cpfinal\storage\dbmodels;

class DbAccount {
    function __construct (
        public string $id,
        public string $email,
        public string $pass,
        public string $firstName,
        public string $lastName,
        public string $position,
        public int $score,
    ) {}
}
