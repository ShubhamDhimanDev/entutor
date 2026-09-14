<?php

namespace App\Enums;

enum RoleplayDomain: string
{
    case PhoneMessages = 'phone_messages';
    case ShoppingMoney = 'shopping_money';
    case HealthEverydayHelp = 'health_everyday_help';
    case ProblemsComplaints = 'problems_complaints';
    case SocialFamily = 'social_family';
}
