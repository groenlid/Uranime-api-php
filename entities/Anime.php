<?php
/**
 * @Entity @Table(name="anime")
 **/
class Anime
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    /** @Column(type="string") **/
    protected $title;
}
