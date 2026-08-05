import { useForm, Head, Link } from '@inertiajs/react';
import { MapPin, Calendar, Edit, Settings, Heart, Users, Package } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AppLayout } from '@/components/app-shell';
import { Textarea } from '@/components/ui/textarea';

interface User {
    id: number;
    name: string;
    email: string;
    profile_photo_url: string | null;
    profile?: {
        bio: string | null;
        phone: string | null;
        city: string | null;
        country: string | null;
        birth_date: string | null;
        website: string | null;
        facebook_url: string | null;
        twitter_url: string | null;
        instagram_url: string | null;
        is_public: boolean;
    };
}

interface Props {
    user: User;
    my_donations: any[];
    my_issues: any[];
    friend_count: number;
    donation_count: number;
    issue_count: number;
}

export default function MyProfile({ user, my_donations, my_issues, friend_count, donation_count, issue_count }: Props) {
    const [editMode, setEditMode] = useState(false);
    const { data, setData, put, processing } = useForm({
        name: user.name,
        bio: user.profile?.bio || '',
        phone: user.profile?.phone || '',
        city: user.profile?.city || '',
        country: user.profile?.country || '',
        birth_date: user.profile?.birth_date || '',
        website: user.profile?.website || '',
        facebook_url: user.profile?.facebook_url || '',
        twitter_url: user.profile?.twitter_url || '',
        instagram_url: user.profile?.instagram_url || '',
        is_public: user.profile?.is_public ?? true,
    });

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        put('/profile');
        setEditMode(false);
    };

    return (
        <AppLayout>
            <Head title="My Profile" />

            <div className="space-y-6">
                {/* Profile Header */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col md:flex-row gap-6">
                            {/* Avatar */}
                            <div className="flex-shrink-0">
                                {user.profile_photo_url ? (
                                    <img
                                        src={user.profile_photo_url}
                                        alt={user.name}
                                        className="w-32 h-32 rounded-full object-cover"
                                    />
                                ) : (
                                    <div className="w-32 h-32 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span className="text-4xl font-medium">
                                            {user.name.charAt(0)}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Info */}
                            <div className="flex-1">
                                {editMode ? (
                                    <form onSubmit={handleSave} className="space-y-4">
                                        <Input
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            label="Name"
                                        />
                                        <Textarea
                                            value={data.bio}
                                            onChange={(e) => setData('bio', e.target.value)}
                                            label="Bio"
                                            rows={3}
                                        />
                                        <div className="grid grid-cols-2 gap-4">
                                            <Input
                                                value={data.city}
                                                onChange={(e) => setData('city', e.target.value)}
                                                label="City"
                                            />
                                            <Input
                                                value={data.country}
                                                onChange={(e) => setData('country', e.target.value)}
                                                label="Country"
                                            />
                                        </div>
                                        <div className="flex gap-2">
                                            <Button type="submit" disabled={processing}>
                                                Save Changes
                                            </Button>
                                            <Button type="button" variant="outline" onClick={() => setEditMode(false)}>
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                ) : (
                                    <>
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <h1 className="text-2xl font-bold">{user.name}</h1>
                                                <p className="text-muted-foreground">{user.email}</p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Link href="/settings/profile">
                                                    <Button variant="outline" size="sm">
                                                        <Settings className="mr-2 h-4 w-4" />
                                                        Settings
                                                    </Button>
                                                </Link>
                                                <Button variant="outline" size="sm" onClick={() => setEditMode(true)}>
                                                    <Edit className="mr-2 h-4 w-4" />
                                                    Edit Profile
                                                </Button>
                                            </div>
                                        </div>

                                        {user.profile?.bio && (
                                            <p className="mt-4">{user.profile.bio}</p>
                                        )}

                                        <div className="flex flex-wrap gap-4 mt-4 text-sm text-muted-foreground">
                                            {user.profile?.city && (
                                                <div className="flex items-center gap-1">
                                                    <MapPin className="h-4 w-4" />
                                                    {user.profile.city}
                                                </div>
                                            )}
                                            {user.profile?.birth_date && (
                                                <div className="flex items-center gap-1">
                                                    <Calendar className="h-4 w-4" />
                                                    Born {new Date(user.profile.birth_date).toLocaleDateString()}
                                                </div>
                                            )}
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Stats */}
                <div className="grid grid-cols-3 gap-4">
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <Users className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                            <p className="text-2xl font-bold">{friend_count}</p>
                            <p className="text-sm text-muted-foreground">Friends</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <Heart className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                            <p className="text-2xl font-bold">{donation_count}</p>
                            <p className="text-sm text-muted-foreground">Donations</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <Package className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                            <p className="text-2xl font-bold">{issue_count}</p>
                            <p className="text-sm text-muted-foreground">Issues Created</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Activity Tabs */}
                <Tabs defaultValue="donations" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="donations">My Donations</TabsTrigger>
                        <TabsTrigger value="issues">My Issues</TabsTrigger>
                    </TabsList>

                    <TabsContent value="donations">
                        <Card>
                            <CardHeader>
                                <CardTitle>Donation History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {my_donations.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">
                                        No donations yet
                                    </p>
                                ) : (
                                    <div className="space-y-4">
                                        {my_donations.map((donation) => (
                                            <div key={donation.id} className="flex items-center gap-4 p-4 border rounded-lg">
                                                <div className="flex-1">
                                                    <p className="font-semibold text-green-600">{donation.amount}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        To: {donation.donation_issue?.title}
                                                    </p>
                                                </div>
                                                <Badge>{donation.status}</Badge>
                                                <span className="text-sm text-muted-foreground">
                                                    {new Date(donation.created_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="issues">
                        <Card>
                            <CardHeader>
                                <CardTitle>Issues I've Created</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {my_issues.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">
                                        No issues created yet
                                    </p>
                                ) : (
                                    <div className="space-y-4">
                                        {my_issues.map((issue) => (
                                            <Link key={issue.id} href={`/issues/${issue.id}`}>
                                                <div className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                    <div className="flex-1">
                                                        <p className="font-medium">{issue.title}</p>
                                                        <p className="text-sm text-muted-foreground line-clamp-1">
                                                            {issue.description}
                                                        </p>
                                                    </div>
                                                    <Badge>{issue.status}</Badge>
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}