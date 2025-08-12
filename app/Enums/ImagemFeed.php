<?php

namespace App\Enums;

enum ImagemFeed:string
{
    case  MASTER = "master";
    case  NO_WATERMARK = "sem_marca_dagua";
    case  THBPRIMARY = "primario";
    case  THBSECUNDARY = "secundario";
    case  VIDEO = "video";
}
